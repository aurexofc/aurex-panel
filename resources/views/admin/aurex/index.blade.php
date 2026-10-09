@extends('layouts.admin')

@section('title')
    Aurex Control Center
@endsection

@section('content-header')
    <h1>Aurex Control Center<small>Coins, store plans, ads & referrals — everything in one place.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Aurex</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-coins"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Coins in Circulation</span>
                <span class="info-box-number">{{ number_format($stats['coins']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-cube"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Active Plans</span>
                <span class="info-box-number">{{ $stats['plans'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-play-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ads Watched Today</span>
                <span class="info-box-number">{{ $stats['ads_today'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-purple"><i class="fa fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Users</span>
                <span class="info-box-number">{{ number_format($stats['users']) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-md-4">
        <div class="box box-warning">
            <div class="box-header with-border"><h3 class="box-title">Store Plans</h3></div>
            <div class="box-body">
                <p class="text-muted">Create and price the server plans users buy with coins.</p>
                <a href="{{ route('admin.aurex.plans') }}" class="btn btn-warning btn-block">Manage Plans</a>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-4">
        <div class="box box-warning">
            <div class="box-header with-border"><h3 class="box-title">PreBots</h3></div>
            <div class="box-body">
                <p class="text-muted">Pre-made WhatsApp bots users deploy with coins.</p>
                <a href="{{ route('admin.aurex.prebots') }}" class="btn btn-warning btn-block">Manage PreBots</a>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-4">
        <div class="box box-warning">
            <div class="box-header with-border"><h3 class="box-title">🎟️ Redeem Codes</h3></div>
            <div class="box-body">
                <p class="text-muted">Generate VIP codes — users claim them for coins.</p>
                <a href="{{ route('admin.aurex.redeem-codes') }}" class="btn btn-warning btn-block">Manage Codes</a>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-4">
        <div class="box box-warning">
            <div class="box-header with-border"><h3 class="box-title">Coins</h3></div>
            <div class="box-body">
                <p class="text-muted">Search any user and grant or deduct coins manually.</p>
                <a href="{{ route('admin.aurex.coins') }}" class="btn btn-warning btn-block">Manage Coins</a>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-4">
        <div class="box box-warning">
            <div class="box-header with-border"><h3 class="box-title">Ads & Referrals</h3></div>
            <div class="box-body">
                <p class="text-muted">One-click setup: rewards, limits and your ad network embed code.</p>
                <a href="{{ route('admin.aurex.ads') }}" class="btn btn-warning btn-block">Configure</a>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-4">
        <div class="box box-success">
            <div class="box-header with-border"><h3 class="box-title">Coin Top-Ups</h3></div>
            <div class="box-body">
                <p class="text-muted">Manual payments via Easypaisa, JazzCash, USDT & Binance. Review requests and credit coins.</p>
                <a href="{{ route('admin.aurex.topups') }}" class="btn btn-success btn-block">Manage Top-Ups</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Recent Coin Activity</h3></div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Reason</th>
                            <th>When</th>
                        </tr>
                        @foreach ($recent_ledger as $entry)
                            <tr>
                                <td>{{ $entry->user->username ?? '—' }}</td>
                                <td><strong class="{{ $entry->amount >= 0 ? 'text-green' : 'text-red' }}">{{ ($entry->amount >= 0 ? '+' : '') . number_format($entry->amount) }}</strong></td>
                                <td><code>{{ $entry->reason }}</code></td>
                                <td>{{ $entry->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
