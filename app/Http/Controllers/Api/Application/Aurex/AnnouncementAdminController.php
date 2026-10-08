<?php

namespace Pterodactyl\Http\Controllers\Api\Application\Aurex;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Pterodactyl\Http\Controllers\Api\Application\ApplicationApiController;
use Pterodactyl\Models\Announcement;

/**
 * Admin management of Aurex announcements (application API key required).
 */
class AnnouncementAdminController extends ApplicationApiController
{
    public function index(): array
    {
        return [
            'announcements' => Announcement::query()->latest()->get(),
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $announcement = Announcement::query()->create(
            $request->validate(Announcement::$validationRules)
        );

        return new JsonResponse(['announcement' => $announcement], JsonResponse::HTTP_CREATED);
    }

    public function update(Request $request, Announcement $announcement): array
    {
        $announcement->update($request->validate(Announcement::$validationRules));

        return ['announcement' => $announcement->fresh()];
    }

    public function delete(Announcement $announcement): JsonResponse
    {
        $announcement->delete();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
