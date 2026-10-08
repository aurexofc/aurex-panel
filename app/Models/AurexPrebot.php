<?php

namespace Pterodactyl\Models;

/**
 * Aurex pre-made WhatsApp/Discord/Telegram bots deployable with coins.
 */
class AurexPrebot extends Model
{
    protected $table = 'aurex_prebots';

    protected $fillable = [
        'name', 'slug', 'description', 'github_url', 'icon',
        'price_coins', 'memory', 'disk', 'cpu', 'egg_id',
        'featured', 'active', 'sort_order',
    ];

    protected $casts = [
        'price_coins' => 'integer',
        'memory' => 'integer',
        'disk' => 'integer',
        'cpu' => 'integer',
        'featured' => 'boolean',
        'active' => 'boolean',
    ];

    public static array $validationRules = [
        'name' => 'required|string|max:191',
        'slug' => 'required|string|max:191|alpha_dash',
        'description' => 'nullable|string|max:1000',
        'github_url' => 'required|string|max:500',
        'icon' => 'nullable|string|max:16',
        'price_coins' => 'required|integer|min:0',
        'memory' => 'required|integer|min:128',
        'disk' => 'required|integer|min:512',
        'cpu' => 'required|integer|min:50',
        'egg_id' => 'nullable|integer|exists:eggs,id',
        'featured' => 'boolean',
        'active' => 'boolean',
        'sort_order' => 'integer|min:0',
    ];
}
