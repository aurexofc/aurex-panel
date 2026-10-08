<?php

namespace Pterodactyl\Models;

/**
 * A coin top-up package users can buy via manual payment.
 *
 * @property int $id
 * @property string $name
 * @property int $coins
 * @property int $price
 * @property bool $active
 * @property int $sort_order
 */
class TopupPackage extends Model
{
    public const RESOURCE_NAME = 'aurex_topup_package';

    protected $table = 'aurex_topup_packages';

    /**
     * Use the integer ID for route binding (no uuid column on aurex tables).
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    protected $fillable = [
        'name',
        'coins',
        'price',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function requests()
    {
        return $this->hasMany(TopupRequest::class, 'package_id');
    }
}
