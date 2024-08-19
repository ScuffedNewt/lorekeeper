@extends('world.layout')

@section('title')
    Weather
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Weather' => 'world/weathers']) !!}
    <h1>Weather</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    {!! $weathers->render() !!}
    @foreach ($weathers as $weather)
        <div class="card mb-3">
            <div class="card-body">
                @include('world._weather_entry', ['imageUrl' => $weather->imageUrl, 'name' => $weather->displayName, 'description' => $weather->parsed_description, 'searchUrl' => $weather->searchUrl, 'weather' => $weather])
            </div>
        </div>
    @endforeach
    {!! $weathers->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $weathers->total() }} result{{ $weathers->total() == 1 ? '' : 's' }} found.</div>
@endsection
