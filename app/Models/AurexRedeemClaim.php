<?php

namespace Pterodactyl\Models;

/**
 * Records which user claimed which redeem code (one claim per user per code).
 */
class AurexRedeemClaim extends Model
{
    protected $table = 'aurex_redeem_claims';

    public $timestamps = false;

    protected $fillable = [
        'code_id', 'user_id', 'claimed_at',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
    ];

    public function code()
    {
        return $this->belongsTo(AurexRedeemCode::class, 'code_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
