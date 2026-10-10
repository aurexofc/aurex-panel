<?php

namespace Pterodactyl\Models;

/**
 * A server plan sold in the Aurex store for coins.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $memory
 * @property int $cpu
 * @property int $disk
 * @property int $price_coins
 * @property int $duration_days
 * @property int|null $egg_id
 * @property bool $active
 */
class ServerPlan extends Model
{
    public const RESOURCE_NAME = 'aurex_server_plan';

    protected $table = 'aurex_server_plans';

    protected $fillable = [
        'name',
        'description',
        'memory',
        'cpu',
        'disk',
        'price_coins',
        'duration_days',
        'egg_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public static array $validationRules = [
        'name' => 'required|string|min:1|max:191',
        'description' => 'nullable|string',
        'memory' => 'required|integer|min:128',
        'cpu' => 'required|integer|min:10|max:400',
        'disk' => 'required|integer|min:512',
        'price_coins' => 'required|integer|min:0',
        'duration_days' => 'required|integer|min:1|max:365',
        'egg_id' => 'nullable|integer|exists:eggs,id',
        'active' => 'sometimes|boolean',
    ];

    public function egg()
    {
        return $this->belongsTo(Egg::class);
    }

    /**
     * Aurex models use auto-increment id, not uuid, for route binding.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
