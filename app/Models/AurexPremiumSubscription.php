<?php

namespace Pterodactyl\Models;

/**
 * A user's premium subscription. Active while active=true and
 * (expires_at is null for lifetime, or expires_at is in the future).
 */
class AurexPremiumSubscription extends Model
{
    protected $table = 'aurex_premium_subscriptions';

    protected $fillable = [
        'user_id', 'package_id', 'starts_at', 'expires_at', 'active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(AurexPremiumPackage::class, 'package_id');
    }

    /**
     * Whether this subscription currently grants premium benefits.
     */
    public function isLive(): bool
    {
        if (!$this->active) {
            return false;
        }
        if ($this->expires_at === null) {
            return true; // lifetime
        }

        return $this->expires_at->isFuture();
    }
}
