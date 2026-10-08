@extends('layouts.admin')

@section('title')
    Aurex Ads & Referrals
@endsection

@section('content-header')
    <h1>Ads & Referrals<small>One-click setup for rewarded ads and referral bonuses.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.aurex.index') }}">Aurex</a></li>
        <li class="active">Ads & Referrals</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('admin.aurex.ads') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <div class="box box-warning">
                <div class="box-header with-border"><h3 class="box-title">Rewarded Ads</h3></div>
                <div class="box-body">
                                            <label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:10px 0;"><input type="checkbox" name="ads_enabled" value="1" {{ !empty($ads['enabled']) ? 'checked' : '' }} style="width:24px;height:24px;transform:scale(1.6);-webkit-transform:scale(1.6);accent-color:#d4a017;cursor:pointer;flex:0 0 auto;margin:0 8px;"><strong>Ads enabled</strong></label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label>Coins per ad view</label><input type="number" name="ads_reward_coins" class="form-control" required min="0" max="100000" value="{{ $ads['reward_coins'] ?? 100 }}"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group"><label>Demo ad length (seconds)</label><input type="number" name="ads_demo_duration_seconds" class="form-control" required min="5" max="300" value="{{ $ads['demo_duration_seconds'] ?? 20 }}"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group"><label>Max ads / user / day</label><input type="number" name="ads_daily_limit" class="form-control" required min="1" max="1000" value="{{ $ads['daily_limit'] ?? 10 }}"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group"><label>Cooldown (seconds)</label><input type="number" name="ads_cooldown_seconds" class="form-control" required min="0" max="86400" value="{{ $ads['cooldown_seconds'] ?? 60 }}"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group"><label>Max ads / IP / day</label><input type="number" name="ads_ip_daily_limit" class="form-control" required min="1" max="10000" value="{{ $ads['ip_daily_limit'] ?? 25 }}"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Ad network embed code <small class="text-muted">(one-click: paste & save)</small></label>
                        <textarea name="ads_embed_code" class="form-control" rows="6" maxlength="20000" placeholder="Paste your ad network's rewarded-ad HTML/JS here (e.g. Monetag, Adsterra)…">{{ $ads['embed_code'] ?? '' }}</textarea>
                        <p class="help-block">Leave empty to keep the built-in demo ad. When set, users watch your real ad instead — coins are still verified server-side.</p>
                    </div>
                    @if (!empty($ads['embed_code']))
                        <div class="alert alert-success"><i class="fa fa-check"></i> Custom ad code is active.</div>
                    @else
                        <div class="alert alert-info"><i class="fa fa-info-circle"></i> No ad code set — the demo ad is showing.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title">Referral Bonuses</h3></div>
                <div class="box-body">
                                            <label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:10px 0;"><input type="checkbox" name="referrals_enabled" value="1" {{ !empty($referrals['enabled']) ? 'checked' : '' }} style="width:24px;height:24px;transform:scale(1.6);-webkit-transform:scale(1.6);accent-color:#d4a017;cursor:pointer;flex:0 0 auto;margin:0 8px;"><strong>Referrals enabled</strong></label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label>Coins for referrer</label><input type="number" name="referrals_referrer_bonus" class="form-control" required min="0" max="100000" value="{{ $referrals['referrer_bonus'] ?? 500 }}"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group"><label>Coins for invited friend</label><input type="number" name="referrals_referred_bonus" class="form-control" required min="0" max="100000" value="{{ $referrals['referred_bonus'] ?? 200 }}"></div>
                        </div>
                    </div>
                    <p class="text-muted">Users share their invite code from the Store; both sides earn coins when a friend claims it. Anti-abuse: each user can only claim one code.</p>
                </div>
            </div>

            <div class="box box-danger">
                <div class="box-header with-border"><h3 class="box-title">Registration — Welcome Bonus & Anti-Abuse</h3></div>
                <div class="box-body">
                                            <label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:10px 0;"><input type="checkbox" name="reg_welcome_bonus_enabled" value="1" {{ !empty($registration['welcome_bonus_enabled']) ? 'checked' : '' }} style="width:24px;height:24px;transform:scale(1.6);-webkit-transform:scale(1.6);accent-color:#d4a017;cursor:pointer;flex:0 0 auto;margin:0 8px;"><strong>Welcome bonus enabled</strong></label>
                    <div class="form-group"><label>Welcome bonus coins (one-time per new user)</label><input type="number" name="reg_welcome_bonus_coins" class="form-control" required min="0" max="100000" value="{{ $registration['welcome_bonus_coins'] ?? 500 }}"></div>
                                            <label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:10px 0;"><input type="checkbox" name="reg_one_per_ip" value="1" {{ !empty($registration['one_per_ip']) ? 'checked' : '' }} style="width:24px;height:24px;transform:scale(1.6);-webkit-transform:scale(1.6);accent-color:#d4a017;cursor:pointer;flex:0 0 auto;margin:0 8px;"><strong>One account per IP address</strong></label>
                        <p class="help-block" style="margin-left:20px">Blocks a second signup from an IP that already registered an account.</p>
                    </div>
                                            <label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:10px 0;"><input type="checkbox" name="reg_block_vpn" value="1" {{ !empty($registration['block_vpn']) ? 'checked' : '' }} style="width:24px;height:24px;transform:scale(1.6);-webkit-transform:scale(1.6);accent-color:#d4a017;cursor:pointer;flex:0 0 auto;margin:0 8px;"><strong>Block VPN / proxy registrations</strong></label>
                        <p class="help-block" style="margin-left:20px">Uses a free IP reputation check; blocks most commercial VPNs. Results cached 24h; never blocks when the check service is down.</p>
                    </div>
                </div>
            </div>

            <div class="box box-success">
                <div class="box-body text-center">
                    <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-check"></i> Save All Settings</button>
                    <p class="text-muted" style="margin-top:8px">Changes apply immediately — no rebuild needed.</p>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
