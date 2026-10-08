<?php

namespace Pterodactyl\Models;

/**
 * A user's manual top-up request. Status: pending, approved, rejected.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $package_id
 * @property int $coins
 * @property int $price
 * @property string $method
 * @property string $transaction_ref
 * @property string|null $whatsapp
 * @property string|null $email
 * @property string $status
 * @property string|null $admin_note
 * @property int|null $reviewed_by
 */
class TopupRequest extends Model
{
    public const RESOURCE_NAME = 'aurex_topup_request';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $table = 'aurex_topup_requests';

    /**
     * Use the integer ID for route binding (no uuid column on aurex tables).
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    protected $fillable = [
        'user_id',
        'package_id',
        'coins',
        'price',
        'method',
        'transaction_ref',
        'whatsapp',
        'email',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(TopupPackage::class, 'package_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
