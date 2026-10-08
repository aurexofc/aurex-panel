<?php

namespace Pterodactyl\Http\Controllers\Admin\Aurex;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Models\AurexPrebot;
use Pterodactyl\Models\Egg;
use Pterodactyl\Http\Controllers\Controller;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;

class PrebotController extends Controller
{
    public function __construct(
        protected AlertsMessageBag $alert,
        protected ViewFactory $view,
    ) {
    }

    public function index(): View
    {
        return $this->view->make('admin.aurex.prebots', [
            'prebots' => AurexPrebot::orderByDesc('featured')->orderBy('sort_order')->get(),
            'eggs' => Egg::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(AurexPrebot::$validationRules);
        $data['active'] = $request->boolean('active', true);
        $data['featured'] = $request->boolean('featured', false);
        if (empty($data['egg_id'])) {
            $data['egg_id'] = null;
        }
        if (empty($data['icon'])) {
            $data['icon'] = '🤖';
        }

        AurexPrebot::create($data);

        $this->alert->success('PreBot created. It is now visible in the PreBots tab.')->flash();

        return redirect()->route('admin.aurex.prebots');
    }

    public function update(Request $request, AurexPrebot $prebot): RedirectResponse
    {
        $data = $request->validate(AurexPrebot::$validationRules);
        $data['active'] = $request->boolean('active');
        $data['featured'] = $request->boolean('featured');
        if (empty($data['egg_id'])) {
            $data['egg_id'] = null;
        }

        $prebot->update($data);

        $this->alert->success("PreBot \"{$prebot->name}\" updated.")->flash();

        return redirect()->route('admin.aurex.prebots');
    }

    public function destroy(AurexPrebot $prebot): RedirectResponse
    {
        $prebot->delete();

        $this->alert->success("PreBot \"{$prebot->name}\" deleted.")->flash();

        return redirect()->route('admin.aurex.prebots');
    }
}
