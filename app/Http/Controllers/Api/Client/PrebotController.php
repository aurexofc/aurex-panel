<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Models\Allocation;
use Pterodactyl\Models\AurexPrebot;
use Pterodactyl\Models\Egg;
use Pterodactyl\Services\Servers\ServerCreationService;

/**
 * Aurex PreBots: users deploy pre-made WhatsApp bots with coins.
 */
class PrebotController extends ClientApiController
{
    public function __construct(private ServerCreationService $creationService)
    {
        parent::__construct();
    }

    /**
     * List active prebots plus the caller's coin balance.
     */
    public function index(Request $request): array
    {
        return [
            'balance' => $request->user()->coins_balance,
            'prebots' => AurexPrebot::query()
                ->where('active', true)
                ->orderByDesc('featured')
                ->orderBy('sort_order')
                ->orderBy('price_coins')
                ->get(),
        ];
    }

    /**
     * Deploy a prebot with coins. Deducts coins, provisions a real
     * server with the bot's egg, refunds on failure.
     */
    public function purchase(Request $request): JsonResponse
    {
        $data = $request->validate([
            'prebot_id' => 'required|integer|exists:aurex_prebots,id',
            'name' => 'required|string|min:1|max:191',
        ]);

        $user = $request->user();
        $prebot = AurexPrebot::query()->where('active', true)->findOrFail($data['prebot_id']);

        if ($user->coins_balance < $prebot->price_coins) {
            throw new DisplayException('Insufficient coins. Watch ads or invite friends to earn more.');
        }

        $allocation = Allocation::query()->whereNull('server_id')->orderBy('id')->first();
        if (!$allocation) {
            throw new DisplayException('No server capacity available right now. Please try again later.');
        }

        $egg = $prebot->egg_id ? Egg::query()->find($prebot->egg_id) : Egg::query()->first();
        if (!$egg) {
            throw new DisplayException('No server type is configured yet. Please contact support.');
        }

        $images = $egg->docker_images ?? [];
        $image = is_array($images) ? reset($images) : $images;
        if (!$image) {
            throw new DisplayException('The selected server type has no docker image. Please contact support.');
        }

        // Deduct first so a double-submit cannot create two servers.
        $user->spendCoins($prebot->price_coins, 'prebot_purchase', [
            'prebot_id' => $prebot->id,
            'prebot_name' => $prebot->name,
        ]);

        try {
            $server = $this->creationService->handle([
                'name' => $data['name'],
                'owner_id' => $user->id,
                'egg_id' => $egg->id,
                'docker_image' => $image,
                'startup' => $egg->startup,
                'environment' => [
                    'BOT_REPO' => $prebot->github_url,
                    'BOT_NAME' => $prebot->name,
                ],
                'allocation_id' => $allocation->id,
                'memory' => $prebot->memory,
                'swap' => 0,
                'io' => 500,
                'cpu' => $prebot->cpu,
                'disk' => $prebot->disk,
                'database_limit' => 0,
                'allocation_limit' => 0,
                'backup_limit' => 1,
                'description' => "Aurex PreBot: {$prebot->name}",
            ]);
        } catch (\Throwable $exception) {
            $user->awardCoins($prebot->price_coins, 'refund_prebot_failed', [
                'prebot_id' => $prebot->id,
                'error' => $exception->getMessage(),
            ]);

            throw new DisplayException('Bot deployment failed and your coins were refunded. Please try again.');
        }

        return new JsonResponse([
            'balance' => $user->coins_balance,
            'server_id' => $server->uuid,
            'message' => "Your {$prebot->name} is deploying! Check your server console for the pairing code or QR to link WhatsApp.",
        ], JsonResponse::HTTP_CREATED);
    }
}
