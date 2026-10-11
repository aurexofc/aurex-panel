@extends('layouts.admin')

@section('title')
    Aurex Sub-Admins — Asif OFC Protection
@endsection

@section('content-header')
    <h1>🛡️ Sub-Admins<small>Asif OFC Protection — limited read-only admin access.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.aurex.index') }}">Aurex</a></li>
        <li class="active">Sub-Admins</li>
    </ol>
@endsection

@section('content')
<style>
    .aurex-gold-card { border-top: 3px solid #d4af37; }
    .aurex-stat { text-align: center; padding: 12px 0; }
    .aurex-stat .num { font-size: 28px; font-weight: 700; color: #d4af37; }
    .aurex-stat .lbl { color: #888; font-size: 12px; text-transform: uppercase; }
    .perm-list { margin: 0; padding-left: 18px; color: #666; font-size: 13px; }
    .perm-list li { margin-bottom: 4px; }
    .perm-no { color: #c0392b; }
    .perm-yes { color: #27ae60; }
</style>

{{-- Stats --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box aurex-gold-card"><div class="aurex-stat"><div class="num">🛡️ {{ $total }}</div><div class="lbl">Active sub-admins</div></div></div>
    </div>
</div>

{{-- Grant sub-admin --}}
<div class="row">
    <div class="col-md-6">
        <div class="box aurex-gold-card">
            <div class="box-header with-border">
                <h3 class="box-title">🛡️ Grant Sub-Admin Access</h3>
            </div>
            <div class="box-body">
                <form method="POST" action="{{ route('admin.aurex.subadmins.grant') }}">
                    @csrf
                    <div class="form-group">
                        <label for="identifier">Email or username</label>
                        <input type="text" name="identifier" id="identifier" class="form-control" placeholder="Email or username" required>
                    </div>
                    <button type="submit" class="btn btn-warning">Grant Sub-Admin</button>
                    <span class="help-block" style="margin-top:8px">Only you (super admin) can grant this. Sub-admins cannot grant others.</span>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">What sub-admins can / cannot do</h3>
            </div>
            <div class="box-body">
                <ul class="perm-list">
                    <li class="perm-yes">✅ View server list & server details</li>
                    <li class="perm-yes">✅ Browse the Aurex overview</li>
                    <li class="perm-no">⛔ Cannot create / edit / delete servers</li>
                    <li class="perm-no">⛔ Cannot touch users, coins, plans, ads, settings</li>
                    <li class="perm-no">⛔ Cannot manage nodes, locations, databases</li>
                    <li class="perm-no">⛔ Blocked pages show "Access denied by Asif OFC protection"</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Sub-admin list --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">🛡️ Sub-Admins</h3>
                <div class="box-tools">
                    <form method="GET" action="{{ route('admin.aurex.subadmins') }}" class="form-inline">
                        <div class="input-group input-group-sm" style="width:240px">
                            <input type="text" name="q" class="form-control pull-right" placeholder="Search user or email" value="{{ request('q') }}">
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
                            <th>Granted</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        @forelse ($subAdmins as $admin)
                            <tr>
                                <td>
                                    <strong>{{ $admin->username }}</strong><br>
                                    <small class="text-muted">{{ $admin->email }}</small>
                                </td>
                                <td>{{ $admin->updated_at?->format('Y-m-d') ?? '—' }}</td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('admin.aurex.subadmins.revoke', $admin->id) }}" style="display:inline" onsubmit="return confirm('Revoke sub-admin access for {{ $admin->username }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-danger">Revoke Access</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted" style="padding:24px">No sub-admins yet. Grant access above. 👑</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($subAdmins->hasPages())
                <div class="box-footer clearfix">
                    {{ $subAdmins->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
