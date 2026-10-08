<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Pterodactyl\Models\Announcement;

/**
 * Announcements currently visible to users.
 */
class AnnouncementController extends ClientApiController
{
    public function index(): array
    {
        return [
            'announcements' => Announcement::currentlyActive()->map(fn (Announcement $a) => [
                'id' => $a->id,
                'title' => $a->title,
                'body' => $a->body,
                'created_at' => $a->created_at,
            ]),
        ];
    }
}
