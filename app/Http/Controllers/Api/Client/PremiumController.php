<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Models\AurexPremiumPackage;
use Pterodactyl\Models\AurexPremiumSubscription;

/**
 * Aurex Premium: users buy Weekly/Monthly/Yearly/Lifetime with coins.
 * Premium unlocks ad-free panel, up to 10 servers, and the 👑 PREMIUM badge.
 */
class PremiumController extends ClientApiController
{
    /**
     * List active packages plus the caller's balance and premium status.
     */
    public function index(Request $request): array
    {
        $user = $request->user();
        $subscription = $user->premiumSubscription()->with('package')->first();

        return [
            'balance' => $user->coins_balance,
            'is_premium' => $user->is_premium,
            'max_servers' => $user->max_servers,
            'subscription' => $subscription ? [
                'package_name' => $subscription->package?->name,
                'expires_at' => $subscription->expires_at?->toIso8601String(),
                'is_lifetime' => $subscription->expires_at === null,
            ] : null,
            'packages' => AurexPremiumPackage::query()
                ->where('active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'duration_days' => $p->duration_days,
                    'duration_label' => $p->durationLabel(),
                    'is_lifetime' => $p->isLifetime(),
                    'price_coins' => $p->price_coins,
                    'max_servers' => $p->max_servers,
                    'ads_free' => $p->ads_free,
                ]),
        ];
    }

    /**
     * Buy a premium package with coins. Extends an existing live
     * subscription instead of stacking a second one.
     */
    public function purchase(Request $request): JsonResponse
    {
        $data = $request->validate([
            'package_id' => 'required|integer|exists:aurex_premium_packages,id',
        ]);

        $user = $request->user();
        $package = AurexPremiumPackage::query()->where('active', true)->findOrFail($data['package_id']);

        if ($user->coins_balance < $package->price_coins) {
            throw new DisplayException('Insufficient coins. Top up or earn more coins first.');
        }

        $subscription = DB::transaction(function () use ($user, $package) {
            $user->spendCoins($package->price_coins, 'premium_purchase', [
                'package_id' => $package->id,
                'package_name' => $package->name,
            ]);

            $existing = $user->premiumSubscription()->first();

            if ($existing) {
                // Extend the current subscription.
                $base = $existing->expires_at && $existing->expires_at->isFuture()
                    ? $existing->expires_at
                    : now();
                $existing->update([
                    'package_id' => $package->id,
                    'expires_at' => $package->isLifetime() ? null : $base->copy()->addDays($package->duration_days),
                    'active' => true,
                ]);

                return $existing->fresh();
            }

            return AurexPremiumSubscription::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'starts_at' => now(),
                'expires_at' => $package->isLifetime() ? null : now()->addDays($package->duration_days),
                'active' => true,
            ]);
        });

        return new JsonResponse([
            'balance' => $user->coins_balance,
            'is_premium' => true,
            'expires_at' => $subscription->expires_at?->toIso8601String(),
            'is_lifetime' => $subscription->expires_at === null,
            'message' => "Welcome to AUREX Premium! 👑 Ad-free panel, {$package->max_servers} VIP servers, and your shiny PREMIUM badge.",
        ], JsonResponse::HTTP_CREATED);
    }
}
