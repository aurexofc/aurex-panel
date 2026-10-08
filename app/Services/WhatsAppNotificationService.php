<?php

namespace Pterodactyl\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Models\TopupRequest;

/**
 * WhatsApp notifications for coin top-ups.
 *
 * Two providers:
 *  - wasphere (recommended): self-hosted WaSphere on your VPS.
 *    Sends to ANY number (admin + users). Setup: run wasphere-install.sh
 *    on the VPS, scan QR, create API key.
 *  - callmebot: free CallMeBot API (admin self-alerts only — free plan
 *    cannot message other numbers). Send "I allow callmebot to send me
 *    messages" to +34 623 78 64 49 on WhatsApp to get an API key.
 *
 * Notifications must NEVER break the main flow — all sends are wrapped
 * and failures are only logged.
 */
class WhatsAppNotificationService
{
    private const CALLMEBOT_URL = 'https://api.callmebot.com/whatsapp.php';

    /**
     * Normalize a Pakistani mobile number to CallMeBot format (92XXXXXXXXXX).
     * Accepts 03001234567, 3001234567, 923001234567, +923001234567.
     */
    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '92') && strlen($digits) === 12) {
            return $digits;
        }
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '92' . substr($digits, 1);
        }
        if (strlen($digits) === 10 && str_starts_with($digits, '3')) {
            return '92' . $digits;
        }

        return $digits;
    }

    public static function isValidPakistaniNumber(string $phone): bool
    {
        return (bool) preg_match('/^0?3\d{9}$/', preg_replace('/[\s\-]/', '', $phone));
    }

    private function provider(): string
    {
        return (string) config('aurex.topup.notifications.provider', 'wasphere');
    }

    private function isEnabled(): bool
    {
        if (!(bool) config('aurex.topup.notifications.enabled', false)) {
            return false;
        }

        if ($this->provider() === 'wasphere') {
            return !empty(config('aurex.topup.notifications.wasphere_url'))
                && !empty(config('aurex.topup.notifications.wasphere_key'));
        }

        return !empty(config('aurex.topup.notifications.apikey'))
            && !empty(config('aurex.topup.notifications.admin_phone'));
    }

    /**
     * Send a WhatsApp message. Routes to the configured provider.
     * Returns true on success. Never throws.
     */
    public function send(string $phone, string $message): bool
    {
        return $this->provider() === 'wasphere'
            ? $this->sendViaWaSphere($phone, $message)
            : $this->sendViaCallMeBot($phone, $message);
    }

    /**
     * Send via self-hosted WaSphere REST API. Works for any recipient.
     */
    private function sendViaWaSphere(string $phone, string $message): bool
    {
        $phone = self::normalizePhone($phone);
        $baseUrl = rtrim((string) config('aurex.topup.notifications.wasphere_url', ''), '/');
        $apiKey = (string) config('aurex.topup.notifications.wasphere_key', '');
        $session = (string) config('aurex.topup.notifications.wasphere_session', '');

        if (empty($phone) || empty($baseUrl) || empty($apiKey) || empty($session)) {
            return false;
        }

        try {
            $response = Http::timeout(15)
                ->withToken($apiKey)
                ->post($baseUrl . '/api/messages/send', [
                    'session' => $session,
                    'to' => $phone,
                    'text' => $message,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('WaSphere notification failed', [
                'phone' => substr($phone, 0, 6) . '******',
                'status' => $response->status(),
                'body' => substr((string) $response->body(), 0, 200),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::warning('WaSphere notification exception: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Send a WhatsApp message via CallMeBot. Admin self-alerts only —
     * the free plan cannot message other numbers. Returns true on success.
     * Never throws.
     */
    private function sendViaCallMeBot(string $phone, string $message): bool
    {
        $phone = self::normalizePhone($phone);
        $apikey = (string) config('aurex.topup.notifications.apikey', '');

        if (empty($phone) || empty($apikey)) {
            return false;
        }

        try {
            $response = Http::timeout(10)->get(self::CALLMEBOT_URL, [
                'phone' => $phone,
                'text' => $message,
                'apikey' => $apikey,
            ]);

            $body = (string) $response->body();
            if ($response->successful() && str_contains($body, 'Message queued')) {
                return true;
            }

            Log::warning('WhatsApp notification failed', [
                'phone' => substr($phone, 0, 6) . '******',
                'status' => $response->status(),
                'body' => substr($body, 0, 200),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::warning('WhatsApp notification exception: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Send a welcome message to a newly registered user.
     * Only works if the user provided a phone number at signup.
     */
    public function notifyWelcome(\Pterodactyl\Models\User $user): bool
    {
        if (empty($user->phone) || !$this->isEnabled()) {
            return false;
        }

        // CallMeBot free plan: only self-messages allowed.
        if ($this->provider() === 'callmebot') {
            return false;
        }

        $bonus = (int) config('aurex.registration.welcome_bonus_coins', 0);
        $panelUrl = rtrim((string) config('app.url'), '/');

        $lines = [
            '👑 *AUREX PANEL* 👑',
            '━━━━━━━━━━━━━━━',
            '🎉 *WELCOME, ' . strtoupper($user->username) . '!*',
            '━━━━━━━━━━━━━━━',
            'Your account has been created successfully!',
        ];

        if ($bonus > 0) {
            $lines[] = '🪙 *' . number_format($bonus) . ' FREE coins* added to your account!';
        }

        $lines[] = '━━━━━━━━━━━━━━━';
        $lines[] = '🖥️ Login: ' . $panelUrl;
        $lines[] = 'Deploy your first game server today! 🚀';

        return $this->send($user->phone, implode("\n", $lines));
    }

    /**
     * Send a password reset link via WhatsApp.
     * Only works if the user has a phone number on their account.
     */
    public function notifyPasswordReset(\Pterodactyl\Models\User $user, string $token): bool
    {
        if (empty($user->phone) || !$this->isEnabled()) {
            return false;
        }

        // CallMeBot free plan: only self-messages allowed.
        if ($this->provider() === 'callmebot') {
            return false;
        }

        $resetUrl = url('/auth/password/reset/' . $token . '?email=' . urlencode($user->email));

        $message = implode("\n", [
            '👑 *AUREX PANEL* 👑',
            '━━━━━━━━━━━━━━━',
            '🔐 *PASSWORD RESET*',
            '━━━━━━━━━━━━━━━',
            'Hi ' . $user->username . '! You requested a password reset.',
            '',
            'Reset your password here:',
            $resetUrl,
            '',
            '━━━━━━━━━━━━━━━',
            'If you did not request this, just ignore this message.',
        ]);

        return $this->send($user->phone, $message);
    }

    /**
     * Notify the admin about a new top-up request.
     */
    public function notifyAdmin(TopupRequest $request): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $request->loadMissing(['user:id,username', 'package:id,name']);
        $methodLabel = (string) (config("aurex.topup.methods.{$request->method}.label") ?? ucfirst($request->method));
        $adminUrl = rtrim((string) config('app.url'), '/');

        $message = implode("\n", [
            '👑 *AUREX PANEL* 👑',
            '━━━━━━━━━━━━━━━',
            '🔔 *NEW TOP-UP REQUEST*',
            '━━━━━━━━━━━━━━━',
            '👤 *User:* ' . $request->user?->username,
            '📦 *Package:* ' . ($request->package?->name ?? '—'),
            '🪙 *Coins:* ' . number_format((int) $request->coins),
            '💰 *Amount:* ' . \Pterodactyl\Services\CurrencyService::format((int) $request->price),
            '💳 *Method:* ' . $methodLabel,
            '🔢 *Ref:* ' . $request->transaction_ref,
            '📱 *WhatsApp:* ' . ($request->whatsapp ?? '—'),
            '━━━━━━━━━━━━━━━',
            '🖥️ Review: ' . $adminUrl . '/admin/aurex/topups',
        ]);

        return $this->send((string) config('aurex.topup.notifications.admin_phone'), $message);
    }

    /**
     * Notify the user their top-up was approved.
     * Works with WaSphere (any number). CallMeBot free cannot message
     * other numbers, so it is skipped for that provider.
     */
    public function notifyUserApproved(TopupRequest $request): bool
    {
        if (empty($request->whatsapp) || !$this->isEnabled()) {
            return false;
        }

        // CallMeBot free plan: only self-messages allowed.
        if ($this->provider() === 'callmebot') {
            return false;
        }

        $request->loadMissing('package:id,name');

        $message = implode("\n", [
            '🎉 *AUREX PANEL* 🎉',
            '━━━━━━━━━━━━━━━',
            '✅ *TOP-UP APPROVED!*',
            '━━━━━━━━━━━━━━━',
            '🪙 *' . number_format((int) $request->coins) . ' coins* added to your account!',
            '📦 Package: ' . ($request->package?->name ?? '—') . ' (' . \Pterodactyl\Services\CurrencyService::format((int) $request->price) . ')',
            '━━━━━━━━━━━━━━━',
            'Enjoy your servers! 🚀',
        ]);

        return $this->send($request->whatsapp, $message);
    }

    /**
     * Notify the user their top-up was rejected.
     * Works with WaSphere (any number). CallMeBot free cannot message
     * other numbers, so it is skipped for that provider.
     */
    public function notifyUserRejected(TopupRequest $request, ?string $note): bool
    {
        if (empty($request->whatsapp) || !$this->isEnabled()) {
            return false;
        }

        // CallMeBot free plan: only self-messages allowed.
        if ($this->provider() === 'callmebot') {
            return false;
        }

        $request->loadMissing('package:id,name');

        $message = implode("\n", [
            '⚠️ *AUREX PANEL* ⚠️',
            '━━━━━━━━━━━━━━━',
            '❌ *TOP-UP REJECTED*',
            '━━━━━━━━━━━━━━━',
            '📦 Package: ' . ($request->package?->name ?? '—'),
            '📝 Reason: ' . ($note ?: 'Please contact support'),
            '━━━━━━━━━━━━━━━',
            'Try again or contact us for help.',
        ]);

        return $this->send($request->whatsapp, $message);
    }
}
