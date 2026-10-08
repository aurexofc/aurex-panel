<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Models\Allocation;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\ServerPlan;
use Pterodactyl\Services\Servers\ServerCreationService;

/**
 * Aurex store: users spend earned coins on game server plans.
 */
class StoreController extends ClientApiController
{
    public function __construct(private ServerCreationService $creationService)
    {
        parent::__construct();
    }

    /**
     * List active plans plus the caller's coin balance.
     */
    public function index(Request $request): array
    {
        return [
            'balance' => $request->user()->coins_balance,
            'plans' => ServerPlan::query()
                ->where('active', true)
                ->orderBy('price_coins')
                ->get(),
        ];
    }

    /**
     * Recent coin movements for the caller.
     */
    public function ledger(Request $request): array
    {
        return [
            'balance' => $request->user()->coins_balance,
            'entries' => $request->user()->coinLedger()
                ->latest()
                ->limit(50)
                ->get(),
        ];
    }

    /**
     * Buy a plan with coins. Deducts coins, provisions a real server
     * on the first node with a free allocation, refunds on failure.
     */
    public function purchase(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan_id' => 'required|integer|exists:aurex_server_plans,id',
            'name' => 'required|string|min:1|max:191',
        ]);

        $user = $request->user();
        $plan = ServerPlan::query()->where('active', true)->findOrFail($data['plan_id']);

        if ($user->coins_balance < $plan->price_coins) {
            throw new DisplayException('Insufficient coins. Watch ads or invite friends to earn more.');
        }

        $allocation = Allocation::query()->whereNull('server_id')->orderBy('id')->first();
        if (!$allocation) {
            throw new DisplayException('No server capacity available right now. Please try again later.');
        }

        $egg = $plan->egg_id ? Egg::query()->find($plan->egg_id) : Egg::query()->first();
        if (!$egg) {
            throw new DisplayException('No server type is configured yet. Please contact support.');
        }

        $images = $egg->docker_images ?? [];
        $image = is_array($images) ? reset($images) : $images;
        if (!$image) {
            throw new DisplayException('The selected server type has no docker image. Please contact support.');
        }

        // Deduct first so a double-submit cannot create two servers.
        $user->spendCoins($plan->price_coins, 'server_purchase', [
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
        ]);

        try {
            $server = $this->creationService->handle([
                'name' => $data['name'],
                'owner_id' => $user->id,
                'egg_id' => $egg->id,
                'docker_image' => $image,
                'startup' => $egg->startup,
                'environment' => [],
                'allocation_id' => $allocation->id,
                'memory' => $plan->memory,
                'swap' => 0,
                'io' => 500,
                'cpu' => $plan->cpu,
                'disk' => $plan->disk,
                'database_limit' => 0,
                'allocation_limit' => 0,
                'backup_limit' => 0,
                'description' => "Aurex plan: {$plan->name} ({$plan->duration_days} days)",
            ]);
        } catch (\Throwable $exception) {
            // Compensating refund — the creation service already cleans up
            // the half-created server on daemon failures.
            $user->awardCoins($plan->price_coins, 'refund_server_failed', [
                'plan_id' => $plan->id,
                'error' => $exception->getMessage(),
            ]);

            throw new DisplayException('Server creation failed and your coins were refunded. Please try again.');
        }

        return new JsonResponse([
            'balance' => $user->coins_balance,
            'server_id' => $server->uuid,
            'message' => "Your {$plan->name} server is being installed!",
        ], JsonResponse::HTTP_CREATED);
    }
}
