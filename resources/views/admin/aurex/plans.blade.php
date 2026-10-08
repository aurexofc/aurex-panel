@extends('layouts.admin')

@section('title')
    Aurex Store Plans
@endsection

@section('content-header')
    <h1>Store Plans<small>Server plans users buy with coins.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.aurex.index') }}">Aurex</a></li>
        <li class="active">Plans</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Plans</h3>
                <div class="box-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newPlanModal">New Plan</button>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>Name</th>
                            <th>Price</th>
                            <th>CPU</th>
                            <th>RAM</th>
                            <th>Disk</th>
                            <th>Egg</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        @foreach ($plans as $plan)
                            <tr>
                                <td><strong>{{ $plan->name }}</strong><br><small class="text-muted">{{ $plan->description }}</small></td>
                                <td><span class="badge bg-yellow">{{ number_format($plan->price_coins) }} coins</span></td>
                                <td>{{ $plan->cpu }}%</td>
                                <td>{{ $plan->memory }} MB</td>
                                <td>{{ $plan->disk }} MB</td>
                                <td>{{ $plan->egg->name ?? '—' }}</td>
                                <td class="text-center">
                                    @if ($plan->active)
                                        <span class="label label-success">Active</span>
                                    @else
                                        <span class="label label-default">Hidden</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#editPlanModal{{ $plan->id }}">Edit</button>
                                    <form action="{{ route('admin.aurex.plans.delete', $plan->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this plan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-xs btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Create modal --}}
<div class="modal fade" id="newPlanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.aurex.plans') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">New Server Plan</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required maxlength="191"></div>
                    <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>Price (coins)</label><input type="number" name="price_coins" class="form-control" required min="0"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Duration (days)</label><input type="number" name="duration_days" class="form-control" required min="1" max="365" value="30"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Egg</label><select name="egg_id" class="form-control"><option value="">— None —</option>@foreach ($eggs as $egg)<option value="{{ $egg->id }}">{{ $egg->name }}</option>@endforeach</select></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>CPU %</label><input type="number" name="cpu" class="form-control" required min="10" max="400" value="100"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>RAM (MB)</label><input type="number" name="memory" class="form-control" required min="128" value="2048"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Disk (MB)</label><input type="number" name="disk" class="form-control" required min="512" value="10240"></div></div>
                    </div>
                    <div class="checkbox"><label><input type="checkbox" name="active" value="1" checked> Active (visible in store)</label></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit modals --}}
@foreach ($plans as $plan)
<div class="modal fade" id="editPlanModal{{ $plan->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.aurex.plans.update', $plan->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Edit Plan — {{ $plan->name }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required maxlength="191" value="{{ $plan->name }}"></div>
                    <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="2">{{ $plan->description }}</textarea></div>
                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>Price (coins)</label><input type="number" name="price_coins" class="form-control" required min="0" value="{{ $plan->price_coins }}"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Duration (days)</label><input type="number" name="duration_days" class="form-control" required min="1" max="365" value="{{ $plan->duration_days }}"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Egg</label><select name="egg_id" class="form-control"><option value="">— None —</option>@foreach ($eggs as $egg)<option value="{{ $egg->id }}" {{ $plan->egg_id == $egg->id ? 'selected' : '' }}>{{ $egg->name }}</option>@endforeach</select></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>CPU %</label><input type="number" name="cpu" class="form-control" required min="10" max="400" value="{{ $plan->cpu }}"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>RAM (MB)</label><input type="number" name="memory" class="form-control" required min="128" value="{{ $plan->memory }}"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Disk (MB)</label><input type="number" name="disk" class="form-control" required min="512" value="{{ $plan->disk }}"></div></div>
                    </div>
                    <div class="checkbox"><label><input type="checkbox" name="active" value="1" {{ $plan->active ? 'checked' : '' }}> Active (visible in store)</label></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
