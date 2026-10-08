@extends('layouts.admin')

@section('title')
    Aurex Coins
@endsection

@section('content-header')
    <h1>Coins<small>Grant or deduct coins for any user.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.aurex.index') }}">Aurex</a></li>
        <li class="active">Coins</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-md-5">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Find User</h3></div>
            <div class="box-body">
                <form action="{{ route('admin.aurex.coins') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" placeholder="Email or username…" value="{{ $q }}">
                        <span class="input-group-btn"><button class="btn btn-primary" type="submit">Search</button></span>
                    </div>
                </form>
            </div>
        </div>

        @if ($user)
        <div class="box box-warning">
            <div class="box-header with-border"><h3 class="box-title">{{ $user->username }}</h3></div>
            <div class="box-body">
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Current balance:</strong> <span class="badge bg-yellow" style="font-size:14px">{{ number_format($user->coins_balance) }} coins</span></p>
                <hr>
                <form action="{{ route('admin.aurex.coins.grant') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <div class="form-group">
                        <label>Action</label>
                        <select name="direction" class="form-control">
                            <option value="add">Add coins</option>
                            <option value="deduct">Deduct coins</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" name="amount" class="form-control" required min="1" max="1000000">
                    </div>
                    <div class="form-group">
                        <label>Reason / note</label>
                        <input type="text" name="reason" class="form-control" required maxlength="191" placeholder="e.g. Event reward">
                    </div>
                    <button type="submit" class="btn btn-warning btn-block">Apply</button>
                </form>
            </div>
        </div>
        @elseif ($q !== '')
        <div class="alert alert-warning">No user found for "<strong>{{ $q }}</strong>".</div>
        @endif
    </div>

    <div class="col-xs-12 col-md-7">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">{{ $user ? 'Recent activity — ' . $user->username : 'Select a user to see their coin history' }}</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr><th>Amount</th><th>Reason</th><th>When</th></tr>
                        @forelse ($ledger as $entry)
                            <tr>
                                <td><strong class="{{ $entry->amount >= 0 ? 'text-green' : 'text-red' }}">{{ ($entry->amount >= 0 ? '+' : '') . number_format($entry->amount) }}</strong></td>
                                <td><code>{{ $entry->reason }}</code>@if(!empty($entry->meta['note']))<br><small class="text-muted">{{ $entry->meta['note'] }}</small>@endif</td>
                                <td>{{ $entry->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">Nothing here yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
