<?php

namespace Pterodactyl\Http\Controllers\Admin\Aurex;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Http\Controllers\Controller;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class AdController extends Controller
{
    public function __construct(
        protected AlertsMessageBag $alert,
        protected ViewFactory $view,
        protected SettingsRepositoryInterface $settings,
    ) {
    }

    public function index(): View
    {
        $ads = config('aurex.ads');
        $referrals = config('aurex.referrals');
        $registration = config('aurex.registration');

        return $this->view->make('admin.aurex.ads', [
            'ads' => $ads,
            'referrals' => $referrals,
            'registration' => $registration,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ads_enabled' => 'sometimes|boolean',
            'ads_reward_coins' => 'required|integer|min:0|max:100000',
            'ads_daily_limit' => 'required|integer|min:1|max:1000',
            'ads_cooldown_seconds' => 'required|integer|min:0|max:86400',
            'ads_ip_daily_limit' => 'required|integer|min:1|max:10000',
            'ads_demo_duration_seconds' => 'required|integer|min:5|max:300',
            'ads_embed_code' => 'nullable|string|max:20000',
            'referrals_enabled' => 'sometimes|boolean',
            'referrals_referrer_bonus' => 'required|integer|min:0|max:100000',
            'referrals_referred_bonus' => 'required|integer|min:0|max:100000',
            'reg_welcome_bonus_enabled' => 'sometimes|boolean',
            'reg_welcome_bonus_coins' => 'required|integer|min:0|max:100000',
            'reg_one_per_ip' => 'sometimes|boolean',
            'reg_block_vpn' => 'sometimes|boolean',
        ]);

        $map = [
            'ads_enabled' => 'aurex:ads:enabled',
            'ads_reward_coins' => 'aurex:ads:reward_coins',
            'ads_daily_limit' => 'aurex:ads:daily_limit',
            'ads_cooldown_seconds' => 'aurex:ads:cooldown_seconds',
            'ads_ip_daily_limit' => 'aurex:ads:ip_daily_limit',
            'ads_demo_duration_seconds' => 'aurex:ads:demo_duration_seconds',
            'ads_embed_code' => 'aurex:ads:embed_code',
            'referrals_enabled' => 'aurex:referrals:enabled',
            'referrals_referrer_bonus' => 'aurex:referrals:referrer_bonus',
            'referrals_referred_bonus' => 'aurex:referrals:referred_bonus',
            'reg_welcome_bonus_enabled' => 'aurex:registration:welcome_bonus_enabled',
            'reg_welcome_bonus_coins' => 'aurex:registration:welcome_bonus_coins',
            'reg_one_per_ip' => 'aurex:registration:one_per_ip',
            'reg_block_vpn' => 'aurex:registration:block_vpn',
        ];

        $booleans = ['ads_enabled', 'referrals_enabled', 'reg_welcome_bonus_enabled', 'reg_one_per_ip', 'reg_block_vpn'];
        foreach ($map as $input => $key) {
            if (in_array($input, $booleans)) {
                $value = $request->boolean($input) ? '1' : '0';
            } else {
                $value = (string) ($data[$input] ?? '');
            }
            $this->settings->set('settings::' . $key, $value);
        }

        $this->alert->success('Advertising, referral & registration settings saved. Changes apply immediately.')->flash();

        return redirect()->route('admin.aurex.ads');
    }
}
