<?php

namespace Pterodactyl\Http\Controllers\Admin\Aurex;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Models\AurexRedeemCode;
use Pterodactyl\Http\Controllers\Controller;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use Carbon\CarbonImmutable;

class RedeemCodeController extends Controller
{
    public function __construct(
        protected AlertsMessageBag $alert,
        protected ViewFactory $view,
    ) {
    }

    public function index(): View
    {
        return $this->view->make('admin.aurex.redeem-codes', [
            'codes' => AurexRedeemCode::orderByDesc('id')->paginate(25),
        ]);
    }

    /**
     * Generate a batch of random VIP codes.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'prefix' => 'nullable|string|max:12|alpha_dash',
            'count' => 'required|integer|min:1|max:500',
            'coins' => 'required|integer|min:1|max:1000000',
            'max_uses' => 'required|integer|min:1|max:100000',
            'expires_in_days' => 'nullable|integer|min:1|max:3650',
        ]);

        $prefix = strtoupper($data['prefix'] ?? 'AUREX');
        $expiresAt = !empty($data['expires_in_days'])
            ? CarbonImmutable::now()->addDays($data['expires_in_days'])
            : null;

        for ($i = 0; $i < $data['count']; $i++) {
            AurexRedeemCode::create([
                'code' => AurexRedeemCode::generateCode($prefix),
                'coins' => $data['coins'],
                'max_uses' => $data['max_uses'],
                'expires_at' => $expiresAt,
                'active' => true,
                'created_by' => $request->user()->id,
            ]);
        }

        $this->alert->success("{$data['count']} redeem code(s) generated with {$data['coins']} coins each.")->flash();

        return redirect()->route('admin.aurex.redeem-codes');
    }

    public function toggle(AurexRedeemCode $code): RedirectResponse
    {
        $code->update(['active' => !$code->active]);

        $state = $code->active ? 'activated' : 'deactivated';
        $this->alert->success("Code {$code->code} {$state}.")->flash();

        return redirect()->route('admin.aurex.redeem-codes');
    }

    public function destroy(AurexRedeemCode $code): RedirectResponse
    {
        $code->delete();

        $this->alert->success("Code {$code->code} deleted.")->flash();

        return redirect()->route('admin.aurex.redeem-codes');
    }
}
