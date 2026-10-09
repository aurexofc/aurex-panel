<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Models\AurexRedeemClaim;
use Pterodactyl\Models\AurexRedeemCode;

/**
 * Aurex redeem codes: users claim a VIP code to receive coins.
 */
class RedeemCodeController extends ClientApiController
{
    /**
     * Claim a redeem code. One claim per user per code.
     */
    public function claim(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:64',
        ]);

        $user = $request->user();
        $code = strtoupper(trim($data['code']));

        $redeemCode = AurexRedeemCode::query()->where('code', $code)->first();
        if (!$redeemCode) {
            throw new DisplayException('Invalid redeem code.');
        }

        if (!$redeemCode->isUsable()) {
            throw new DisplayException('This code has expired or reached its usage limit.');
        }

        $alreadyClaimed = AurexRedeemClaim::query()
            ->where('code_id', $redeemCode->id)
            ->where('user_id', $user->id)
            ->exists();
        if ($alreadyClaimed) {
            throw new DisplayException('You have already claimed this code.');
        }

        // Atomic claim: increment usage and record the claim in one transaction
        // so concurrent requests cannot double-spend a limited code.
        DB::transaction(function () use ($redeemCode, $user) {
            $updated = AurexRedeemCode::query()
                ->where('id', $redeemCode->id)
                ->whereRaw('used_count < max_uses')
                ->increment('used_count');

            if (!$updated) {
                throw new DisplayException('This code just reached its usage limit.');
            }

            AurexRedeemClaim::create([
                'code_id' => $redeemCode->id,
                'user_id' => $user->id,
                'claimed_at' => now(),
            ]);

            $user->awardCoins($redeemCode->coins, 'redeem_code', [
                'code_id' => $redeemCode->id,
                'code' => $redeemCode->code,
            ]);
        });

        return new JsonResponse([
            'balance' => $user->coins_balance,
            'coins' => $redeemCode->coins,
            'message' => "Code claimed! {$redeemCode->coins} coins added to your balance. 🎉",
        ]);
    }
}
