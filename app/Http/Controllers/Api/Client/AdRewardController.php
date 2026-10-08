<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Models\AdReward;

/**
 * Aurex rewarded ads: watch an ad, earn coins.
 *
 * Flow: status -> start (one-time token) -> [ad is shown] -> complete (token).
 * Coins are only awarded server-side after all anti-abuse checks pass.
 */
class AdRewardController extends ClientApiController
{
    /**
     * Whether the user can watch an ad right now.
     */
    public function status(Request $request): array
    {
        // Temporary diagnostic (v17): log config + any failure to a dedicated file.
        @file_put_contents(
            storage_path('logs/aurex-ads-debug.log'),
            '[' . now()->toDateTimeString() . '] ads config: ' . json_encode(config('aurex.ads')) . "\n",
            FILE_APPEND
        );
        try {
            return $this->buildStatus($request);
        } catch (\Throwable $e) {
            @file_put_contents(
                storage_path('logs/aurex-ads-debug.log'),
                '[' . now()->toDateTimeString() . '] status() FAILED: ' . get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n",
                FILE_APPEND
            );
            throw $e;
        }
    }

    /**
     * Ads config with proper PHP types. Values stored via the settings
     * database come back as strings; Carbon 3 strictly requires int|float
     * for addSeconds()/addMinutes(), so normalize here.
     */
    private function adsConfig(): array
    {
        $cfg = config('aurex.ads');
        foreach (['reward_coins', 'daily_limit', 'cooldown_seconds', 'ip_daily_limit', 'demo_duration_seconds', 'token_ttl_minutes'] as $key) {
            $cfg[$key] = (int) ($cfg[$key] ?? 0);
        }
        $cfg['enabled'] = (bool) ($cfg['enabled'] ?? false);

        return $cfg;
    }

    private function buildStatus(Request $request): array
    {
        $cfg = $this->adsConfig();
        $user = $request->user();
        $today = CarbonImmutable::today();

        $watchedToday = AdReward::query()
            ->where('user_id', $user->id)
            ->where('verified', true)
            ->where('watched_at', '>=', $today)
            ->count();

        $last = AdReward::query()
            ->where('user_id', $user->id)
            ->where('verified', true)
            ->latest('watched_at')
            ->first();

        $cooldownEndsAt = $last?->watched_at
            ? CarbonImmutable::parse($last->watched_at)->addSeconds($cfg['cooldown_seconds'])
            : null;

        $now = CarbonImmutable::now();
        $canWatch = (bool) $cfg['enabled']
            && $watchedToday < $cfg['daily_limit']
            && (!$cooldownEndsAt || $cooldownEndsAt->lessThanOrEqualTo($now));

        return [
            'enabled' => (bool) $cfg['enabled'],
            'can_watch' => $canWatch,
            'reward_coins' => (int) $cfg['reward_coins'],
            'watched_today' => $watchedToday,
            'daily_limit' => (int) $cfg['daily_limit'],
            'cooldown_seconds' => (int) $cfg['cooldown_seconds'],
            'cooldown_ends_in' => $cooldownEndsAt && $cooldownEndsAt->greaterThan($now)
                ? $cooldownEndsAt->diffInSeconds($now)
                : 0,
            'has_provider_ad' => !empty($cfg['embed_code']),
            'demo_duration_seconds' => (int) $cfg['demo_duration_seconds'],
        ];
    }

    /**
     * Begin an ad view. Returns a one-time token the client must send back
     * to "complete" after the ad finishes.
     */
    public function start(Request $request): JsonResponse
    {
        $cfg = $this->adsConfig();
        if (!$cfg['enabled']) {
            throw new DisplayException('Rewarded ads are currently disabled.');
        }

        $status = $this->status($request);
        if (!$status['can_watch']) {
            throw new DisplayException('You cannot watch another ad right now. Please try again later.');
        }

        $reward = AdReward::query()->create([
            'user_id' => $request->user()->id,
            'token' => Str::random(48),
            'reward_coins' => (int) $cfg['reward_coins'],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'expires_at' => CarbonImmutable::now()->addMinutes($cfg['token_ttl_minutes']),
        ]);

        return new JsonResponse([
            'token' => $reward->token,
            'expires_at' => $reward->expires_at->toIso8601String(),
            'reward_coins' => $reward->reward_coins,
            'ad_html' => $cfg['embed_code'] ?: null,
            'demo_duration_seconds' => (int) $cfg['demo_duration_seconds'],
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Complete an ad view: verify the token and award coins.
     */
    public function complete(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required|string|max:64',
        ]);

        $cfg = $this->adsConfig();
        $user = $request->user();
        $today = CarbonImmutable::today();

        $result = DB::transaction(function () use ($data, $cfg, $user, $request, $today) {
            /** @var AdReward|null $reward */
            $reward = AdReward::query()
                ->where('token', $data['token'])
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$reward || $reward->verified) {
                throw new DisplayException('This ad reward has already been claimed or is invalid.');
            }

            if (CarbonImmutable::parse($reward->expires_at)->isPast()) {
                throw new DisplayException('This ad session expired. Please start a new one.');
            }

            // Daily limit per user.
            $watchedToday = AdReward::query()
                ->where('user_id', $user->id)
                ->where('verified', true)
                ->where('watched_at', '>=', $today)
                ->count();

            if ($watchedToday >= $cfg['daily_limit']) {
                throw new DisplayException('Daily ad limit reached. Come back tomorrow!');
            }

            // Cooldown between views.
            $last = AdReward::query()
                ->where('user_id', $user->id)
                ->where('verified', true)
                ->latest('watched_at')
                ->first();

            if ($last && CarbonImmutable::parse($last->watched_at)->addSeconds($cfg['cooldown_seconds'])->isFuture()) {
                throw new DisplayException('Please wait a little before watching another ad.');
            }

            // Daily limit per IP across all users (bot/farm protection).
            $ipToday = AdReward::query()
                ->where('ip_address', $request->ip())
                ->where('verified', true)
                ->where('watched_at', '>=', $today)
                ->count();

            if ($ipToday >= $cfg['ip_daily_limit']) {
                throw new DisplayException('Too many ad views from your network today.');
            }

            $reward->update([
                'verified' => true,
                'watched_at' => CarbonImmutable::now(),
            ]);

            $entry = $user->awardCoins($reward->reward_coins, 'ad_watch', [
                'ad_reward_id' => $reward->id,
            ]);

            return [
                'coins_earned' => $reward->reward_coins,
                'balance' => $user->coins_balance,
                'entry_id' => $entry->id,
            ];
        });

        return new JsonResponse($result);
    }
}
