<?php

namespace Pterodactyl\Http\Controllers\Admin\Aurex;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Models\ServerPlan;
use Pterodactyl\Models\Egg;
use Pterodactyl\Http\Controllers\Controller;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;

class PlanController extends Controller
{
    public function __construct(
        protected AlertsMessageBag $alert,
        protected ViewFactory $view,
    ) {
    }

    public function index(): View
    {
        return $this->view->make('admin.aurex.plans', [
            'plans' => ServerPlan::with('egg')->orderBy('price_coins')->get(),
            'eggs' => Egg::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(ServerPlan::$validationRules);
        $data['active'] = $request->boolean('active', true);
        if (empty($data['egg_id'])) {
            $data['egg_id'] = null;
        }

        ServerPlan::create($data);

        $this->alert->success('Server plan created. It is now visible in the Store.')->flash();

        return redirect()->route('admin.aurex.plans');
    }

    public function update(Request $request, ServerPlan $plan): RedirectResponse
    {
        $data = $request->validate(ServerPlan::$validationRules);
        $data['active'] = $request->boolean('active');
        if (empty($data['egg_id'])) {
            $data['egg_id'] = null;
        }

        $plan->update($data);

        $this->alert->success("Plan \"{$plan->name}\" updated.")->flash();

        return redirect()->route('admin.aurex.plans');
    }

    public function destroy(ServerPlan $plan): RedirectResponse
    {
        $plan->delete();

        $this->alert->success("Plan \"{$plan->name}\" deleted.")->flash();

        return redirect()->route('admin.aurex.plans');
    }
}
