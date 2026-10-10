@extends('layouts.admin')

@section('title')
    Aurex Premium Users
@endsection

@section('content-header')
    <h1>Premium Users<small>Who has live premium — grant or revoke directly.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.aurex.index') }}">Aurex</a></li>
        <li><a href="{{ route('admin.aurex.premium') }}">Premium</a></li>
        <li class="active">Users</li>
    </ol>
@endsection

@section('content')
<style>
    .aurex-gold-card { border-top: 3px solid #d4af37; }
    .aurex-stat { text-align: center; padding: 12px 0; }
    .aurex-stat .num { font-size: 28px; font-weight: 700; color: #d4af37; }
    .aurex-stat .lbl { color: #888; font-size: 12px; text-transform: uppercase; }
</style>

{{-- Stats --}}
<div class="row">
    <div class="col-xs-4">
        <div class="box aurex-gold-card"><div class="aurex-stat"><div class="num">{{ $stats['total'] }}</div><div class="lbl">Live premium users</div></div></div>
    </div>
    <div class="col-xs-4">
        <div class="box aurex-gold-card"><div class="aurex-stat"><div class="num">{{ $stats['lifetime'] }}</div><div class="lbl">Lifetime</div></div></div>
    </div>
    <div class="col-xs-4">
        <div class="box aurex-gold-card"><div class="aurex-stat"><div class="num">{{ $stats['expiring'] }}</div><div class="lbl">Expiring in 7 days</div></div></div>
    </div>
</div>

{{-- Grant premium --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box aurex-gold-card">
            <div class="box-header with-border">
                <h3 class="box-title">👑 Grant Premium Directly</h3>
            </div>
            <div class="box-body">
                <form method="POST" action="{{ route('admin.aurex.premium.users.grant') }}" class="form-inline">
                    @csrf
                    <div class="form-group" style="margin-right:10px">
                        <label class="sr-only" for="identifier">Email or username</label>
                        <input type="text" name="identifier" id="identifier" class="form-control" placeholder="Email or username" required style="min-width:260px">
                    </div>
                    <div class="form-group" style="margin-right:10px">
                        <label class="sr-only" for="package_id">Package</label>
                        <select name="package_id" id="package_id" class="form-control" required>
                            @foreach ($packages as $package)
                                <option value="{{ $package->id }}">👑 {{ $package->name }} — {{ $package->durationLabel() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning">Grant Premium</button>
                    <span class="help-block" style="margin-top:8px">Same package extends the current expiry; a different package replaces it. No coins are charged.</span>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Live subscriptions --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">👑 Live Premium Users</h3>
                <div class="box-tools">
                    <form method="GET" action="{{ route('admin.aurex.premium.users') }}" class="form-inline">
                        <div class="input-group input-group-sm" style="width:240px">
                            <input type="text" name="q" class="form-control pull-right" placeholder="Search user or email" value="{{ $search }}">
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>User</th>
                            <th>Package</th>
                            <th>Since</th>
                            <th>Expires</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        @forelse ($subscriptions as $sub)
                            <tr>
                                <td>
                                    <strong>{{ $sub->user?->username ?? 'user #' . $sub->user_id }}</strong><br>
                                    <small class="text-muted">{{ $sub->user?->email }}</small>
                                </td>
                                <td><span class="badge bg-yellow">👑 {{ $sub->package?->name ?? '—' }}</span></td>
                                <td>{{ $sub->starts_at?->format('Y-m-d') ?? '—' }}</td>
                                <td>
                                    @if ($sub->expires_at === null)
                                        <span class="label label-success">Lifetime</span>
                                    @else
                                        {{ $sub->expires_at->format('Y-m-d') }}
                                        @if ($sub->expires_at->isPast())
                                            <span class="label label-danger">Expired</span>
                                        @elseif ($sub->expires_at->lessThan(now()->addDays(7)))
                                            <span class="label label-warning">Soon</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('admin.aurex.premium.users.revoke', $sub->id) }}" style="display:inline" onsubmit="return confirm('Revoke premium for {{ $sub->user?->username ?? 'this user' }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-danger">Remove Premium</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted" style="padding:24px">No live premium users{{ $search ? ' matching "' . e($search) . '"' : '' }} yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($subscriptions->hasPages())
                <div class="box-footer clearfix">
                    <div class="pull-right">{{ $subscriptions->links() }}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
