<?php

namespace Pterodactyl\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Pterodactyl\Mail\TopupApprovedMail;
use Pterodactyl\Mail\TopupRejectedMail;
use Pterodactyl\Mail\TopupRequestNotification;
use Pterodactyl\Mail\TopupTestMail;
use Pterodactyl\Models\TopupRequest;

/**
 * Email notifications for coin top-ups via Gmail SMTP.
 *
 * Setup (free): Google Account → 2-Step Verification → App Password
 * (myaccount.google.com/apppasswords) → paste into admin settings.
 *
 * Notifications must NEVER break the main flow — all sends are wrapped
 * and failures are only logged.
 */
class EmailNotificationService
{
    private function isEnabled(): bool
    {
        return (bool) config('aurex.topup.email_notifications.enabled', false)
            && !empty(config('aurex.topup.email_notifications.to'))
            && !empty(config('aurex.topup.email_notifications.smtp_user'))
            && !empty(config('aurex.topup.email_notifications.smtp_pass'));
    }

    /**
     * Configure the SMTP mailer at runtime from Aurex settings.
     */
    private function configureMailer(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.port' => 587,
            'mail.mailers.smtp.encryption' => 'tls',
            'mail.mailers.smtp.username' => (string) config('aurex.topup.email_notifications.smtp_user'),
            'mail.mailers.smtp.password' => (string) config('aurex.topup.email_notifications.smtp_pass'),
            'mail.from.address' => (string) config('aurex.topup.email_notifications.smtp_user'),
            'mail.from.name' => 'Aurex Panel',
        ]);
    }

    /**
     * Notify the admin about a new top-up request. Returns true on success.
     * Never throws.
     */
    public function sendTopupNotification(TopupRequest $topup): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $this->configureMailer();
            $to = (string) config('aurex.topup.email_notifications.to');

            Mail::mailer('smtp')->to($to)->send(new TopupRequestNotification($topup));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Top-up email notification failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Notify the user their top-up was approved. Simple text email.
     * Never throws.
     */
    public function sendApprovalEmail(TopupRequest $topup): bool
    {
        $topup->loadMissing(['user:id,username,email', 'package:id,name']);
        // Prefer the real email collected at request time; fall back to the account email.
        $email = $topup->email ?: $topup->user?->email;

        if (empty($email) || !$this->isEnabled()) {
            return false;
        }

        try {
            $this->configureMailer();
            $coins = number_format((int) $topup->coins);
            $package = $topup->package?->name ?? '—';
            $username = $topup->user?->username ?? 'there';

            Mail::mailer('smtp')->to($email)->send(new TopupApprovedMail($topup, $username, $coins, $package));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Top-up approval email failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Notify the user their top-up was rejected. Simple text email.
     * Never throws.
     */
    public function sendRejectionEmail(TopupRequest $topup, ?string $note): bool
    {
        $topup->loadMissing(['user:id,username,email', 'package:id,name']);
        // Prefer the real email collected at request time; fall back to the account email.
        $email = $topup->email ?: $topup->user?->email;

        if (empty($email) || !$this->isEnabled()) {
            return false;
        }

        try {
            $this->configureMailer();
            $package = $topup->package?->name ?? '—';
            $username = $topup->user?->username ?? 'there';

            Mail::mailer('smtp')->to($email)->send(new TopupRejectedMail($topup, $username, $package, $note));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Top-up rejection email failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Send a test email to the admin address. Returns true on success.
     * Never throws.
     */
    public function sendTestEmail(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $this->configureMailer();
            $to = (string) config('aurex.topup.email_notifications.to');

            Mail::mailer('smtp')->to($to)->send(new TopupTestMail());

            return true;
        } catch (\Throwable $e) {
            Log::warning('Top-up test email failed: ' . $e->getMessage());

            return false;
        }
    }
}
