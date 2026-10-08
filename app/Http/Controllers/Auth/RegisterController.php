<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Ramsey\Uuid\Uuid;
use Illuminate\View\View;
use Pterodactyl\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Pterodactyl\Rules\Username;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Exceptions\DisplayException;

/**
 * Public self-service registration for the Aurex panel.
 *
 * Unlike admin-created users, registrants choose their own password and are
 * logged in immediately — no "set your password" email is sent.
 *
 * Anti-abuse: one account per IP address and VPN/proxy blocking (both
 * toggleable from Admin → Aurex → Ads & Referrals). New users receive a
 * one-time welcome coin bonus.
 */
class RegisterController extends AbstractLoginController
{
    protected int $welcomeBonusAwarded = 0;

    public function index(): View
    {
        return view('templates/auth.core');
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     * @throws DisplayException
     */
    public function register(Request $request, Hasher $hasher): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:32', 'unique:users,username', new Username()],
            'email' => 'required|email:rfc|max:191|unique:users,email',
            'password' => 'required|string|min:8|max:191|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $ip = $request->ip();
        $this->assertAllowedToRegister($ip);

        /** @var User $user */
        $user = new User([
            'username' => $data['username'],
            'email' => mb_strtolower($data['email']),
            'phone' => !empty($data['phone'])
                ? \Pterodactyl\Services\WhatsAppNotificationService::normalizePhone($data['phone'])
                : null,
            'password' => $hasher->make($data['password']),
            'name_first' => $data['username'],
            'name_last' => $data['username'],
            'registration_ip' => $ip,
            'language' => 'en',
            'root_admin' => false,
            'use_totp' => false,
        ]);
        // uuid is not mass-assignable, set it directly.
        $user->uuid = Uuid::uuid4()->toString();
        $user->save();

        $bonus = (int) config('aurex.registration.welcome_bonus_coins', 0);
        if (config('aurex.registration.welcome_bonus_enabled', true) && $bonus > 0) {
            $user->awardCoins($bonus, 'Welcome bonus');
            $this->welcomeBonusAwarded = $bonus;
        }

        // Send a WhatsApp welcome message if the user provided a phone number.
        if (!empty($user->phone)) {
            try {
                app(\Pterodactyl\Services\WhatsAppNotificationService::class)->notifyWelcome($user);
            } catch (\Throwable) {
                // Never break registration.
            }
        }

        Activity::event('auth:register')
            ->withRequestMetadata()
            ->subject($user)
            ->property(['email' => $user->email, 'username' => $user->username, 'ip' => $ip])
            ->log();

        return $this->sendLoginResponse($user, $request);
    }

    /**
     * Include the awarded welcome bonus in the login payload so the
     * frontend can celebrate it with the user.
     */
    protected function sendLoginResponse(User $user, Request $request): JsonResponse
    {
        $response = parent::sendLoginResponse($user, $request);

        if ($this->welcomeBonusAwarded > 0) {
            $data = $response->getData(true);
            $data['data']['welcome_bonus'] = $this->welcomeBonusAwarded;
            $response->setData($data);
        }

        return $response;
    }

    /**
     * Enforce the one-account-per-IP and no-VPN rules.
     *
     * @throws DisplayException
     */
    protected function assertAllowedToRegister(?string $ip): void
    {
        if (config('aurex.registration.one_per_ip', true) && $ip) {
            $exists = User::query()->where('registration_ip', $ip)->exists();
            if ($exists) {
                throw new DisplayException('An account has already been created from this network. Only one account per connection is allowed.');
            }
        }

        if (config('aurex.registration.block_vpn', true) && $ip && $this->isVpn($ip)) {
            throw new DisplayException('Registrations from VPN or proxy connections are not allowed. Please turn off your VPN and try again.');
        }
    }

    /**
     * Check whether an IP belongs to a known VPN/proxy using a free
     * reputation API. Results are cached for 24h. Fails open (allows
     * registration) if the API is unreachable, and skips private IPs.
     */
    protected function isVpn(string $ip): bool
    {
        // Never block local development / private network addresses.
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        return (bool) Cache::remember("aurex:vpn:{$ip}", 86400, function () use ($ip) {
            try {
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,proxy,hosting,query',
                ]);

                if (!$response->successful()) {
                    return false;
                }

                $data = $response->json();

                return ($data['status'] ?? '') === 'success' && ($data['proxy'] ?? false) === true;
            } catch (\Throwable) {
                // API down or timed out — fail open so legit users are never blocked.
                return false;
            }
        });
    }
}
