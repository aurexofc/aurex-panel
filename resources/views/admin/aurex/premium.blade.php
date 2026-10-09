@extends('layouts.admin')

@section('title')
    Aurex Premium Packages
@endsection

@section('content-header')
    <h1>Premium Packages<small>Weekly / Monthly / Yearly / Lifetime — users buy with coins.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.aurex.index') }}">Aurex</a></li>
        <li class="active">Premium</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">👑 Premium Packages</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>Package</th>
                            <th>Duration</th>
                            <th>Price (coins)</th>
                            <th>Max Servers</th>
                            <th class="text-center">Ad-Free</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        @foreach ($packages as $package)
                            <tr>
                                <td><strong>👑 {{ $package->name }}</strong><br><small class="text-muted">{{ $package->slug }}</small></td>
                                <td>{{ $package->durationLabel() }}</td>
                                <td><span class="badge bg-yellow">{{ number_format($package->price_coins) }} coins</span></td>
                                <td>{{ $package->max_servers }}</td>
                                <td class="text-center">
                                    @if ($package->ads_free)
                                        <span class="label label-success">Yes</span>
                                    @else
                                        <span class="label label-default">No</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($package->active)
                                        <span class="label label-success">Active</span>
                                    @else
                                        <span class="label label-default">Hidden</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-default" data-toggle="modal" data-target="#editPackage{{ $package->id }}">Edit</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach ($packages as $package)
<div class="modal fade" id="editPackage{{ $package->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aurex.premium.update', $package->id) }}">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit {{ $package->name }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $package->name }}" required maxlength="64">
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label>Price (coins)</label>
                                <input type="number" name="price_coins" class="form-control" value="{{ $package->price_coins }}" min="0" required>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label>Duration (days)</label>
                                <input type="number" name="duration_days" class="form-control" value="{{ $package->duration_days }}" min="1" required>
                                <p class="help-block">36500+ = lifetime</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label>Max servers</label>
                                <input type="number" name="max_servers" class="form-control" value="{{ $package->max_servers }}" min="1" max="100" required>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label>Sort order</label>
                                <input type="number" name="sort_order" class="form-control" value="{{ $package->sort_order }}" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="ads_free" value="1" {{ $package->ads_free ? 'checked' : '' }}> Ad-free panel</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="active" value="1" {{ $package->active ? 'checked' : '' }}> Active (visible to users)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
