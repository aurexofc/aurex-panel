<?php

namespace Pterodactyl\Http\Controllers\Admin\Aurex;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Models\User;
use Pterodactyl\Http\Controllers\Controller;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;

/**
 * Asif OFC Protection — Sub-Admin Management.
 * Only the super admin (root_admin) can grant/revoke sub-admin access.
 * Sub-admins get read-only server views; everything else shows
 * "Access denied by Asif OFC protection".
 */
class SubAdminController extends Controller
{
    public function __construct(
        protected AlertsMessageBag $alert,
        protected ViewFactory $view,
    ) {
    }

    public function index(Request $request): View
    {
        $query = User::query()->where('is_sub_admin', true);

        if ($request->filled('q')) {
            $term = '%' . trim($request->input('q')) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('username', 'like', $term)->orWhere('email', 'like', $term);
            });
        }

        $subAdmins = $query->latest('id')->paginate(50)->withQueryString();

        return $this->view->make('admin.aurex.subadmins', [
            'subAdmins' => $subAdmins,
            'total' => User::where('is_sub_admin', true)->count(),
        ]);
    }

    public function grant(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identifier' => 'required|string|max:191',
        ]);

        $identifier = trim($data['identifier']);
        $user = User::where('username', $identifier)->orWhere('email', $identifier)->first();

        if (!$user) {
            $this->alert->danger("User \"{$identifier}\" not found.")->flash();
            return redirect()->route('admin.aurex.subadmins');
        }

        if ($user->root_admin) {
            $this->alert->info("{$user->username} is already a super admin.")->flash();
            return redirect()->route('admin.aurex.subadmins');
        }

        $user->update(['is_sub_admin' => true]);

        $this->alert->success("🛡️ {$user->username} is now a sub-admin (Asif OFC Protection active).")->flash();
        return redirect()->route('admin.aurex.subadmins');
    }

    public function revoke(User $user): RedirectResponse
    {
        $user->update(['is_sub_admin' => false]);

        $this->alert->success("Sub-admin access revoked for {$user->username}.")->flash();
        return redirect()->route('admin.aurex.subadmins');
    }
}
