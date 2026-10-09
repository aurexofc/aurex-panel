<?php

namespace Pterodactyl\Models;

/**
 * Premium subscription packages (Weekly / Monthly / Yearly / Lifetime).
 * Users buy these with coins; premium unlocks ad-free panel, up to
 * max_servers VIP servers, and the 👑 PREMIUM badge.
 */
class AurexPremiumPackage extends Model
{
    protected $table = 'aurex_premium_packages';

    protected $fillable = [
        'name', 'slug', 'duration_days', 'price_coins',
        'max_servers', 'ads_free', 'active', 'sort_order',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'price_coins' => 'integer',
        'max_servers' => 'integer',
        'ads_free' => 'boolean',
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Use the numeric id for route binding (no uuid column on this table).
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function subscriptions()
    {
        return $this->hasMany(AurexPremiumSubscription::class, 'package_id');
    }

    /**
     * Lifetime packages never expire (duration_days >= 36500).
     */
    public function isLifetime(): bool
    {
        return $this->duration_days >= 36500;
    }

    public function durationLabel(): string
    {
        if ($this->isLifetime()) {
            return 'Lifetime';
        }
        if ($this->duration_days >= 365) {
            $years = (int) round($this->duration_days / 365);
            return $years . ' Year' . ($years > 1 ? 's' : '');
        }
        if ($this->duration_days >= 30) {
            $months = (int) round($this->duration_days / 30);
            return $months . ' Month' . ($months > 1 ? 's' : '');
        }

        return $this->duration_days . ' Day' . ($this->duration_days > 1 ? 's' : '');
    }
}
