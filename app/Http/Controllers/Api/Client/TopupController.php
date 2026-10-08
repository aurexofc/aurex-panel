<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Models\TopupPackage;
use Pterodactyl\Models\TopupRequest;
use Pterodactyl\Services\EmailNotificationService;
use Pterodactyl\Services\WhatsAppNotificationService;

/**
 * Aurex manual coin top-ups.
 *
 * Flow: packages -> select package + method -> pay manually ->
 * submit transaction reference -> admin approves -> coins credited.
 */
class TopupController extends ClientApiController
{
    /**
     * Available payment methods with the admin's account details.
     */
    private function methods(): array
    {
        $cfg = config('aurex.topup.methods', []);
        $out = [];
        foreach ($cfg as $key => $m) {
            if (empty($m['account'])) {
                continue;
            }
            $out[] = [
                'key' => $key,
                'label' => $m['label'] ?? ucfirst($key),
                'account' => $m['account'],
                'instructions' => $m['instructions'] ?? '',
            ];
        }

        return $out;
    }

    /**
     * List active packages + payment methods.
     */
    public function index(): JsonResponse
    {
        if (!config('aurex.topup.enabled')) {
            throw new DisplayException('Coin top-ups are currently disabled.');
        }

        $baseCurrency = \Pterodactyl\Services\CurrencyService::code();
        // Auto-detect the visitor's currency from their IP — a user in Spain
        // sees EUR, a user in Pakistan sees PKR, etc.
        $userCurrency = \Pterodactyl\Services\CurrencyService::detectForIp(request()->ip());

        $packages = TopupPackage::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get(['id', 'name', 'coins', 'price'])
            ->map(function ($p) use ($baseCurrency, $userCurrency) {
                $p->price_base = $p->price;
                $p->price = \Pterodactyl\Services\CurrencyService::convert($p->price, $baseCurrency, $userCurrency);

                return $p;
            });

        return response()->json([
            'enabled' => true,
            'packages' => $packages,
            'methods' => $this->methods(),
            'currency' => $userCurrency,
            'currency_symbol' => \Pterodactyl\Services\CurrencyService::CURRENCIES[$userCurrency]['symbol'],
            'base_currency' => $baseCurrency,
            'base_currency_symbol' => \Pterodactyl\Services\CurrencyService::symbol(),
        ]);
    }

    /**
     * Submit a top-up request after paying manually.
     */
    public function store(Request $request): JsonResponse
    {
        if (!config('aurex.topup.enabled')) {
            throw new DisplayException('Coin top-ups are currently disabled.');
        }

        $methods = collect($this->methods())->pluck('key')->all();

        $data = $request->validate([
            'package_id' => 'required|integer|exists:aurex_topup_packages,id',
            'method' => 'required|string|in:' . implode(',', $methods),
            'transaction_ref' => 'required|string|min:4|max:100',
            'whatsapp' => ['required', 'string', 'max:20', 'regex:/^0?3\d{9}$/'],
            'email' => 'required|email|max:255',
        ]);

        $package = TopupPackage::query()->where('active', true)->findOrFail($data['package_id']);
        $user = $request->user();

        // Prevent duplicate pending requests for the same transaction ref.
        $exists = TopupRequest::query()
            ->where('transaction_ref', $data['transaction_ref'])
            ->whereIn('status', [TopupRequest::STATUS_PENDING, TopupRequest::STATUS_APPROVED])
            ->exists();
        if ($exists) {
            throw new DisplayException('This transaction reference has already been submitted.');
        }

        $topup = TopupRequest::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'coins' => $package->coins,
            'price' => $package->price,
            'method' => $data['method'],
            'transaction_ref' => $data['transaction_ref'],
            'whatsapp' => WhatsAppNotificationService::normalizePhone($data['whatsapp']),
            'email' => strtolower(trim($data['email'])),
            'status' => TopupRequest::STATUS_PENDING,
        ]);

        // Notify the admin on WhatsApp — never let this break the request.
        try {
            app(WhatsAppNotificationService::class)->notifyAdmin($topup);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Admin top-up notification failed: ' . $e->getMessage());
        }

        // Notify the admin by email — never let this break the request.
        try {
            app(EmailNotificationService::class)->sendTopupNotification($topup);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Admin top-up email failed: ' . $e->getMessage());
        }

        return response()->json([
            'id' => $topup->id,
            'status' => $topup->status,
            'message' => 'Request submitted! Your coins will be credited after admin verification.',
        ], 201);
    }

    /**
     * The user's own top-up request history.
     */
    public function history(Request $request): JsonResponse
    {
        $requests = TopupRequest::query()
            ->where('user_id', $request->user()->id)
            ->with('package:id,name')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'package' => $r->package?->name,
                'coins' => $r->coins,
                'price' => $r->price,
                'method' => $r->method,
                'transaction_ref' => $r->transaction_ref,
                'status' => $r->status,
                'admin_note' => $r->admin_note,
                'created_at' => $r->created_at->toIso8601String(),
            ]);

        return response()->json(['data' => $requests]);
    }
}
