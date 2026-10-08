<?php

namespace Pterodactyl\Models;

class Referral extends Model
{
    public const RESOURCE_NAME = 'aurex_referral';

    protected $table = 'aurex_referrals';

    protected $fillable = ['referrer_id', 'referred_id', 'rewarded_at'];

    protected $casts = ['rewarded_at' => 'datetime'];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}
