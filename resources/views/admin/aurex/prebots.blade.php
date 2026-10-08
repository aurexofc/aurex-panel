@extends('layouts.admin')

@section('title')
    Aurex PreBots
@endsection

@section('content-header')
    <h1>PreBots<small>Pre-made WhatsApp bots users deploy with coins.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.aurex.index') }}">Aurex</a></li>
        <li class="active">PreBots</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">PreBots</h3>
                <div class="box-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newPrebotModal">New PreBot</button>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>Bot</th>
                            <th>Price</th>
                            <th>RAM</th>
                            <th>Disk</th>
                            <th>GitHub</th>
                            <th class="text-center">Featured</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        @foreach ($prebots as $prebot)
                            <tr>
                                <td><span style="font-size:1.4em">{{ $prebot->icon }}</span> <strong>{{ $prebot->name }}</strong><br><small class="text-muted">{{ Str::limit($prebot->description, 80) }}</small></td>
                                <td><span class="badge bg-yellow">{{ number_format($prebot->price_coins) }} coins</span></td>
                                <td>{{ $prebot->memory }} MB</td>
                                <td>{{ $prebot->disk }} MB</td>
                                <td><small><a href="{{ $prebot->github_url }}" target="_blank" rel="noreferrer">repo ↗</a></small></td>
                                <td class="text-center">
                                    @if ($prebot->featured)
                                        <span class="label label-warning">👑 Featured</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($prebot->active)
                                        <span class="label label-success">Active</span>
                                    @else
                                        <span class="label label-default">Hidden</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-default" data-toggle="modal" data-target="#editPrebot{{ $prebot->id }}">Edit</button>
                                    <form method="POST" action="{{ route('admin.aurex.prebots.delete', $prebot->id) }}" style="display:inline" onsubmit="return confirm('Delete {{ $prebot->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>

                            {{-- Edit modal --}}
                            <div class="modal fade" id="editPrebot{{ $prebot->id }}" tabindex="-1">
                                <div class="modal-dialog"><div class="modal-content">
                                    <form method="POST" action="{{ route('admin.aurex.prebots.update', $prebot->id) }}">
                                        @csrf @method('PATCH')
                                        <div class="modal-header"><h4 class="modal-title">Edit {{ $prebot->name }}</h4></div>
                                        <div class="modal-body">
                                            <div class="form-group"><label>Name</label><input name="name" class="form-control" value="{{ $prebot->name }}" required></div>
                                            <div class="form-group"><label>Slug</label><input name="slug" class="form-control" value="{{ $prebot->slug }}" required></div>
                                            <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="2">{{ $prebot->description }}</textarea></div>
                                            <div class="form-group"><label>GitHub URL</label><input name="github_url" class="form-control" value="{{ $prebot->github_url }}" required></div>
                                            <div class="row">
                                                <div class="col-sm-3"><div class="form-group"><label>Icon</label><input name="icon" class="form-control" value="{{ $prebot->icon }}"></div></div>
                                                <div class="col-sm-3"><div class="form-group"><label>Price (coins)</label><input name="price_coins" type="number" class="form-control" value="{{ $prebot->price_coins }}" min="0" required></div></div>
                                                <div class="col-sm-2"><div class="form-group"><label>RAM MB</label><input name="memory" type="number" class="form-control" value="{{ $prebot->memory }}" min="128" required></div></div>
                                                <div class="col-sm-2"><div class="form-group"><label>Disk MB</label><input name="disk" type="number" class="form-control" value="{{ $prebot->disk }}" min="512" required></div></div>
                                                <div class="col-sm-2"><div class="form-group"><label>CPU %</label><input name="cpu" type="number" class="form-control" value="{{ $prebot->cpu }}" min="50" required></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-6"><div class="form-group"><label>Egg (optional)</label>
                                                    <select name="egg_id" class="form-control"><option value="">Default</option>
                                                    @foreach ($eggs as $egg)<option value="{{ $egg->id }}" {{ $prebot->egg_id == $egg->id ? 'selected' : '' }}>{{ $egg->name }}</option>@endforeach
                                                    </select></div></div>
                                                <div class="col-sm-3"><div class="form-group"><label>Sort order</label><input name="sort_order" type="number" class="form-control" value="{{ $prebot->sort_order }}" min="0"></div></div>
                                            </div>
                                            <div class="checkbox"><label><input type="checkbox" name="featured" value="1" {{ $prebot->featured ? 'checked' : '' }}> Featured</label></div>
                                            <div class="checkbox"><label><input type="checkbox" name="active" value="1" {{ $prebot->active ? 'checked' : '' }}> Active</label></div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                            <button class="btn btn-primary">Save</button>
                                        </div>
                                    </form>
                                </div></div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- New modal --}}
<div class="modal fade" id="newPrebotModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="{{ route('admin.aurex.prebots') }}">
            @csrf
            <div class="modal-header"><h4 class="modal-title">New PreBot</h4></div>
            <div class="modal-body">
                <div class="form-group"><label>Name</label><input name="name" class="form-control" required></div>
                <div class="form-group"><label>Slug</label><input name="slug" class="form-control" placeholder="my-bot" required></div>
                <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="form-group"><label>GitHub URL</label><input name="github_url" class="form-control" placeholder="https://github.com/..." required></div>
                <div class="row">
                    <div class="col-sm-3"><div class="form-group"><label>Icon</label><input name="icon" class="form-control" value="🤖"></div></div>
                    <div class="col-sm-3"><div class="form-group"><label>Price (coins)</label><input name="price_coins" type="number" class="form-control" value="300" min="0" required></div></div>
                    <div class="col-sm-2"><div class="form-group"><label>RAM MB</label><input name="memory" type="number" class="form-control" value="512" min="128" required></div></div>
                    <div class="col-sm-2"><div class="form-group"><label>Disk MB</label><input name="disk" type="number" class="form-control" value="2048" min="512" required></div></div>
                    <div class="col-sm-2"><div class="form-group"><label>CPU %</label><input name="cpu" type="number" class="form-control" value="100" min="50" required></div></div>
                </div>
                <div class="form-group"><label>Egg (optional)</label>
                    <select name="egg_id" class="form-control"><option value="">Default</option>
                    @foreach ($eggs as $egg)<option value="{{ $egg->id }}">{{ $egg->name }}</option>@endforeach
                    </select></div>
                <div class="checkbox"><label><input type="checkbox" name="featured" value="1"> Featured</label></div>
                <div class="checkbox"><label><input type="checkbox" name="active" value="1" checked> Active</label></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Create</button>
            </div>
        </form>
    </div></div>
</div>
@endsection
