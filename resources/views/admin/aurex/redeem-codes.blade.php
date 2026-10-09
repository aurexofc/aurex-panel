@extends('layouts.admin')

@section('title')
    Aurex Redeem Codes
@endsection

@section('content-header')
    <h1>Redeem Codes<small>Generate VIP codes — users claim them for coins.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.aurex.index') }}">Aurex</a></li>
        <li class="active">Redeem Codes</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">🎟️ Redeem Codes</h3>
                <div class="box-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newCodesModal">Generate Codes</button>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>Code</th>
                            <th>Coins</th>
                            <th>Used</th>
                            <th>Expires</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        @forelse ($codes as $code)
                            <tr>
                                <td><code style="font-size:1.1em">{{ $code->code }}</code></td>
                                <td><span class="badge bg-yellow">{{ number_format($code->coins) }} coins</span></td>
                                <td>{{ $code->used_count }} / {{ $code->max_uses }}</td>
                                <td>{{ $code->expires_at ? $code->expires_at->format('Y-m-d') : '—' }}</td>
                                <td class="text-center">
                                    @if ($code->isUsable())
                                        <span class="label label-success">Usable</span>
                                    @else
                                        <span class="label label-default">Expired</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('admin.aurex.redeem-codes.toggle', $code->id) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs {{ $code->active ? 'btn-warning' : 'btn-success' }}">{{ $code->active ? 'Disable' : 'Enable' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.aurex.redeem-codes.delete', $code->id) }}" style="display:inline" onsubmit="return confirm('Delete code {{ $code->code }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted" style="padding:30px">No codes yet. Generate your first batch! 🎟️</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($codes->hasPages())
                <div class="box-footer clearfix">
                    {{ $codes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="newCodesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aurex.redeem-codes') }}">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">🎟️ Generate Redeem Codes</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Code Prefix</label>
                        <input type="text" name="prefix" class="form-control" value="AUREX" maxlength="12" placeholder="AUREX">
                        <p class="help-block">Codes look like PREFIX-XXXXXX</p>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label>How many codes</label>
                                <input type="number" name="count" class="form-control" value="10" min="1" max="500" required>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label>Coins per code</label>
                                <input type="number" name="coins" class="form-control" value="500" min="1" max="1000000" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label>Max uses per code</label>
                                <input type="number" name="max_uses" class="form-control" value="1" min="1" max="100000" required>
                                <p class="help-block">1 = single use, higher = shareable</p>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label>Expires in (days, optional)</label>
                                <input type="number" name="expires_in_days" class="form-control" min="1" max="3650" placeholder="Never">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
