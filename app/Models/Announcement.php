<?php

namespace Pterodactyl\Models;

use Carbon\CarbonImmutable;

class Announcement extends Model
{
    public const RESOURCE_NAME = 'aurex_announcement';

    protected $table = 'aurex_announcements';

    protected $fillable = ['title', 'body', 'active', 'starts_at', 'ends_at'];

    protected $casts = [
        'active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public static array $validationRules = [
        'title' => 'required|string|min:1|max:191',
        'body' => 'required|string|min:1',
        'active' => 'sometimes|boolean',
        'starts_at' => 'nullable|date',
        'ends_at' => 'nullable|date|after_or_equal:starts_at',
    ];

    /**
     * Announcements visible to users right now.
     */
    public static function currentlyActive()
    {
        $now = CarbonImmutable::now();

        return static::query()
            ->where('active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->latest()
            ->get();
    }
}
