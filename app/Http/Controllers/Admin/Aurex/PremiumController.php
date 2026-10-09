<?php

namespace Pterodactyl\Http\Controllers\Admin\Aurex;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Models\AurexPremiumPackage;
use Pterodactyl\Http\Controllers\Controller;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;

class PremiumController extends Controller
{
    public function __construct(
        protected AlertsMessageBag $alert,
        protected ViewFactory $view,
    ) {
    }

    public function index(): View
    {
        return $this->view->make('admin.aurex.premium', [
            'packages' => AurexPremiumPackage::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, AurexPremiumPackage $package): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:64',
            'price_coins' => 'required|integer|min:0',
            'duration_days' => 'required|integer|min:1',
            'max_servers' => 'required|integer|min:1|max:100',
            'sort_order' => 'integer|min:0',
        ]);
        $data['active'] = $request->boolean('active');
        $data['ads_free'] = $request->boolean('ads_free', true);

        $package->update($data);

        $this->alert->success("Premium package \"{$package->name}\" updated.")->flash();

        return redirect()->route('admin.aurex.premium');
    }
}
