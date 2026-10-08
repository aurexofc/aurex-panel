<?php

namespace Pterodactyl\Http\Controllers\Admin\Aurex;

use Illuminate\View\View;
use Pterodactyl\Models\User;
use Pterodactyl\Models\CoinLedger;
use Pterodactyl\Models\ServerPlan;
use Pterodactyl\Models\AdReward;
use Pterodactyl\Http\Controllers\Controller;
use Illuminate\View\Factory as ViewFactory;

class AurexController extends Controller
{
    public function __construct(protected ViewFactory $view)
    {
    }

    public function index(): View
    {
        $coinsInCirculation = (int) CoinLedger::sum('amount');
        $adsWatchedToday = AdReward::whereDate('created_at', today())->where('verified', true)->count();

        return $this->view->make('admin.aurex.index', [
            'stats' => [
                'users' => User::count(),
                'plans' => ServerPlan::where('active', true)->count(),
                'coins' => $coinsInCirculation,
                'ads_today' => $adsWatchedToday,
            ],
            'recent_ledger' => CoinLedger::with('user')->latest()->limit(10)->get(),
        ]);
    }
}
