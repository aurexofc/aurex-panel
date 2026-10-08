<?php

namespace Pterodactyl\Http\Controllers\Admin\Aurex;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Models\User;
use Pterodactyl\Models\CoinLedger;
use Pterodactyl\Http\Controllers\Controller;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;

class CoinController extends Controller
{
    public function __construct(
        protected AlertsMessageBag $alert,
        protected ViewFactory $view,
    ) {
    }

    public function index(Request $request): View
    {
        $user = null;
        if ($request->filled('q')) {
            $q = $request->input('q');
            $user = User::where('email', $q)->orWhere('username', $q)->first();
        }

        return $this->view->make('admin.aurex.coins', [
            'user' => $user,
            'q' => $request->input('q', ''),
            'ledger' => $user
                ? CoinLedger::where('user_id', $user->id)->latest()->limit(20)->get()
                : collect(),
        ]);
    }

    public function grant(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1|max:1000000',
            'direction' => 'required|in:add,deduct',
            'reason' => 'required|string|max:191',
        ]);

        $user = User::findOrFail($data['user_id']);

        if ($data['direction'] === 'add') {
            $user->awardCoins($data['amount'], 'admin_grant', ['note' => $data['reason']]);
            $this->alert->success("{$data['amount']} coins added to {$user->username}.")->flash();
        } else {
            $user->spendCoins($data['amount'], 'admin_deduct', ['note' => $data['reason']]);
            $this->alert->success("{$data['amount']} coins deducted from {$user->username}.")->flash();
        }

        return redirect()->route('admin.aurex.coins', ['q' => $user->email]);
    }
}
