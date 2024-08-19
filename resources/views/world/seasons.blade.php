@extends('world.layout')

@section('title')
    Seasons
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Weather' => 'world/weathers', 'Seasons' => 'world/seasons']) !!}
    <h1>Seasons</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
        </div>
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::select(
                    'sort',
                    [
                        'alpha' => 'Sort Alphabetically (A-Z)',
                        'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                        'newest' => 'Newest First',
                        'oldest' => 'Oldest First',
                    ],
                    Request::get('sort') ?: 'category',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    {!! $seasons->render() !!}
    @foreach ($seasons as $season)
        <div class="card mb-3">
            <div class="card-body">
                @include('world._season_entry', ['name' => $season->name])
            </div>
        </div>
    @endforeach
    {!! $seasons->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $seasons->total() }} result{{ $seasons->total() == 1 ? '' : 's' }} found.</div>
@endsection
