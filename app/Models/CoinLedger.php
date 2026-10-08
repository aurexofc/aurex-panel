<?php

namespace Pterodactyl\Models;

/**
 * Immutable ledger of every coin movement for a user.
 * Balance is always derived: sum(amount) for the user.
 *
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property string $reason
 * @property array|null $meta
 */
class CoinLedger extends Model
{
    public const RESOURCE_NAME = 'aurex_coin_ledger';

    protected $table = 'aurex_coins_ledger';

    protected $fillable = [
        'user_id',
        'amount',
        'reason',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
