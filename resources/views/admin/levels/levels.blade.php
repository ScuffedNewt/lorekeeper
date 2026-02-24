@extends('admin.layout')

@section('admin-title')
    Levels
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', ucfirst($type) . ' Levels' => 'admin/levels/' . $type]) !!}
    <h1>{{ $type }} Levels</h1>

    <p>This is a list of levels in the game.</p>

    @if (!config('lorekeeper.claymores_and_companions.visibility_settings.'.$type.'_levels'))
         <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> {{ ucfirst($type) }} levels are currently set to be hidden. To change this, update the visibility settings in the config.
        </div>
    @endif

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/levels/' . $type . '/create') }}"><i class="fas fa-plus"></i> Create New {{ ucfirst($type) }} Level</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($levels))
        <p>No levels found.</p>
    @else
        <table class="table table-sm category-table">
            <thead>
                <tr>
                    <th>Level Name</th>
                    <th>Next Level</th>
                    <th>EXP required</th>
                    <th>Rewards</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($levels as $level)
                    <tr class="sort-item" data-id="{{ $level->id }}">
                        <td>{{ $level->name }}</td>
                        <td>{{ $level->nextLevel ? $level->nextLevel->name : 'N/A' }}</td>
                        <td>{{ $level->exp_required }}</td>
                        <td>
                            @if (!count($level->rewards))
                                <p>No rewards.</p>
                            @else
                                @foreach ($level->rewards as $reward)
                                    {!! $reward->reward ? $reward->reward->displayName : $reward->rewardable_type !!}
                                    x{{ $reward->quantity }} <br>
                                @endforeach
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/levels/' . strtolower($level->level_type) . '/edit/' . $level->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
