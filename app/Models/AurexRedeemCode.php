<?php

namespace Pterodactyl\Models;

use Illuminate\Support\Str;

/**
 * Admin-generated VIP redeem codes. Users claim a code once and
 * receive coins in their balance (tracked in the coin ledger).
 */
class AurexRedeemCode extends Model
{
    protected $table = 'aurex_redeem_codes';

    protected $fillable = [
        'code', 'coins', 'max_uses', 'used_count',
        'expires_at', 'active', 'created_by',
    ];

    protected $casts = [
        'coins' => 'integer',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Use the numeric id for route binding (no uuid column on this table).
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function claims()
    {
        return $this->hasMany(AurexRedeemClaim::class, 'code_id');
    }

    /**
     * Generate a random VIP-style code, e.g. AUREX-GOLD-8X4K2M.
     */
    public static function generateCode(string $prefix = 'AUREX'): string
    {
        do {
            $code = strtoupper($prefix . '-' . Str::random(6));
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    /**
     * Whether the code can still be claimed right now.
     */
    public function isUsable(): bool
    {
        if (!$this->active) {
            return false;
        }
        if ($this->used_count >= $this->max_uses) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function remainingUses(): int
    {
        return max(0, $this->max_uses - $this->used_count);
    }
}
