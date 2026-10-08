<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Models\Referral;
use Pterodactyl\Models\User;

/**
 * Aurex referrals: share your code, earn when a friend claims it.
 */
class ReferralController extends ClientApiController
{
    /**
     * My referral code + stats.
     */
    public function index(Request $request): array
    {
        $user = $request->user();
        $cfg = config('aurex.referrals');

        $made = $user->referralsMade()->with('referred:id,username')->latest()->get();

        return [
            'enabled' => (bool) $cfg['enabled'],
            'code' => $cfg['enabled'] ? $user->referral_code : null,
            'referrer_bonus' => (int) $cfg['referrer_bonus'],
            'referred_bonus' => (int) $cfg['referred_bonus'],
            'already_claimed' => $user->referredBy()->exists(),
            'total_invited' => $made->count(),
            'invited' => $made->map(fn (Referral $r) => [
                'username' => $r->referred?->username,
                'rewarded_at' => $r->rewarded_at,
            ]),
        ];
    }

    /**
     * Claim a friend's referral code (once per user, ever).
     */
    public function claim(Request $request): JsonResponse
    {
        $cfg = config('aurex.referrals');
        if (!$cfg['enabled']) {
            throw new DisplayException('Referrals are currently disabled.');
        }

        $data = $request->validate([
            'code' => 'required|string|size:8',
        ]);

        $user = $request->user();

        if ($user->referredBy()->exists()) {
            throw new DisplayException('You have already claimed a referral code.');
        }

        $referrer = User::query()
            ->where('aurex_referral_code', strtoupper($data['code']))
            ->first();

        if (!$referrer) {
            throw new DisplayException('That referral code does not exist.');
        }

        if ($referrer->id === $user->id) {
            throw new DisplayException('You cannot use your own referral code.');
        }

        $result = DB::transaction(function () use ($user, $referrer, $cfg) {
            // Re-check inside the transaction to block double submits.
            if ($user->referredBy()->exists()) {
                throw new DisplayException('You have already claimed a referral code.');
            }

            $referral = Referral::query()->create([
                'referrer_id' => $referrer->id,
                'referred_id' => $user->id,
                'rewarded_at' => now(),
            ]);

            $referrer->awardCoins((int) $cfg['referrer_bonus'], 'referral', [
                'referral_id' => $referral->id,
                'referred_user_id' => $user->id,
            ]);

            $user->awardCoins((int) $cfg['referred_bonus'], 'referral_welcome', [
                'referral_id' => $referral->id,
                'referrer_user_id' => $referrer->id,
            ]);

            return [
                'balance' => $user->coins_balance,
                'bonus' => (int) $cfg['referred_bonus'],
            ];
        });

        return new JsonResponse($result, JsonResponse::HTTP_CREATED);
    }
}
