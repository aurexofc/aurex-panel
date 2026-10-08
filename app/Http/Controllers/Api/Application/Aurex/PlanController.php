<?php

namespace Pterodactyl\Http\Controllers\Api\Application\Aurex;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Pterodactyl\Http\Controllers\Api\Application\ApplicationApiController;
use Pterodactyl\Models\ServerPlan;

/**
 * Admin management of Aurex store plans (application API key required).
 */
class PlanController extends ApplicationApiController
{
    public function index(): array
    {
        return [
            'plans' => ServerPlan::query()->orderBy('price_coins')->get(),
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $plan = ServerPlan::query()->create(
            $request->validate(ServerPlan::$validationRules)
        );

        return new JsonResponse(['plan' => $plan], JsonResponse::HTTP_CREATED);
    }

    public function update(Request $request, ServerPlan $plan): array
    {
        $plan->update($request->validate(ServerPlan::$validationRules));

        return ['plan' => $plan->fresh()];
    }

    public function delete(ServerPlan $plan): JsonResponse
    {
        $plan->delete();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Manually grant coins to any user (support / promotions).
     */
    public function grantCoins(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|integer|min:1|max:1000000',
            'reason' => 'sometimes|string|max:191',
        ]);

        $user = \Pterodactyl\Models\User::query()->findOrFail($data['user_id']);
        $entry = $user->awardCoins(
            $data['amount'],
            $data['reason'] ?? 'admin_grant',
            ['granted_by' => 'application_api']
        );

        return new JsonResponse([
            'balance' => $user->coins_balance,
            'entry' => $entry,
        ]);
    }
}
