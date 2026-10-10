<?php

namespace Pterodactyl\Http\Controllers\Admin\Aurex;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Models\User;
use Pterodactyl\Models\AurexPremiumPackage;
use Pterodactyl\Models\AurexPremiumSubscription;
use Pterodactyl\Http\Controllers\Controller;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;

/**
 * Admin Premium User Management: see every user with live premium,
 * grant premium directly (by email or username + package), and
 * revoke premium instantly. No coins are involved.
 */
class PremiumUserController extends Controller
{
    public function __construct(
        protected AlertsMessageBag $alert,
        protected ViewFactory $view,
    ) {
    }

    /**
     * Live premium subscriptions with search + quick stats.
     */
    public function index(Request $request): View
    {
        $query = AurexPremiumSubscription::query()
            ->with(['user', 'package'])
            ->where('active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });

        if ($request->filled('q')) {
            $term = '%' . trim($request->input('q')) . '%';
            $query->whereHas('user', function ($q) use ($term) {
                $q->where('username', 'like', $term)->orWhere('email', 'like', $term);
            });
        }

        $subscriptions = $query->latest('id')->paginate(50)->withQueryString();

        $stats = [
            'total' => AurexPremiumSubscription::query()
                ->where('active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->count(),
            'lifetime' => AurexPremiumSubscription::query()
                ->where('active', true)->whereNull('expires_at')->count(),
            'expiring' => AurexPremiumSubscription::query()
                ->where('active', true)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()->addDays(7))->count(),
        ];

        $packages = AurexPremiumPackage::query()
            ->where('active', true)->orderBy('sort_order')->get();

        return $this->view->make('admin.aurex.premium-users', [
            'subscriptions' => $subscriptions,
            'stats' => $stats,
            'packages' => $packages,
            'search' => $request->input('q', ''),
        ]);
    }

    /**
     * Grant premium to a user by email or username. Extends the existing
     * subscription when it is on the same package; otherwise replaces it.
     */
    public function grant(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identifier' => 'required|string|max:255',
            'package_id' => 'required|integer|exists:aurex_premium_packages,id',
        ]);

        $identifier = trim($data['identifier']);
        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$user) {
            $this->alert->danger("No user found for \"{$identifier}\" (email or username).")->flash();

            return redirect()->route('admin.aurex.premium.users');
        }

        $package = AurexPremiumPackage::findOrFail($data['package_id']);
        $expiresAt = $package->isLifetime() ? null : now()->addDays($package->duration_days);

        $existing = $user->premiumSubscription()->with('package')->first();

        if ($existing && $existing->package_id === $package->id) {
            // Same package: extend from current expiry (or now if already lapsed).
            $base = $existing->expires_at && $existing->expires_at->isFuture()
                ? $existing->expires_at
                : now();
            $existing->update([
                'expires_at' => $package->isLifetime() ? null : $base->copy()->addDays($package->duration_days),
                'active' => true,
            ]);
            $action = 'extended';
        } else {
            // Different (or no) package: retire the old one, start fresh.
            if ($existing) {
                $existing->update(['active' => false]);
            }
            AurexPremiumSubscription::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'starts_at' => now(),
                'expires_at' => $expiresAt,
                'active' => true,
            ]);
            $action = 'granted';
        }

        $expiryText = $package->isLifetime() ? 'lifetime' : 'until ' . $expiresAt->format('Y-m-d H:i');
        $this->alert->success("👑 Premium {$action} for {$user->username} — {$package->name} ({$expiryText}).")->flash();

        return redirect()->route('admin.aurex.premium.users');
    }

    /**
     * Revoke premium immediately (subscription stays in history, marked inactive).
     */
    public function revoke(AurexPremiumSubscription $subscription): RedirectResponse
    {
        $username = $subscription->user?->username ?? 'user #' . $subscription->user_id;
        $subscription->update(['active' => false]);

        $this->alert->success("Premium revoked for {$username}. They are back on the free plan.")->flash();

        return redirect()->route('admin.aurex.premium.users');
    }
}
