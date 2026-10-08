<?php

namespace Pterodactyl\Models;

/**
 * One rewarded-ad view attempt.
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property int $reward_coins
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property bool $verified
 * @property \Illuminate\Support\Carbon|null $watched_at
 * @property \Illuminate\Support\Carbon $expires_at
 */
class AdReward extends Model
{
    public const RESOURCE_NAME = 'aurex_ad_reward';

    protected $table = 'aurex_ad_rewards';

    protected $fillable = [
        'user_id',
        'token',
        'reward_coins',
        'ip_address',
        'user_agent',
        'verified',
        'watched_at',
        'expires_at',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'watched_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
