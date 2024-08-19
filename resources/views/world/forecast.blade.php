@extends('world.layout')

@section('title')
    Weather Forecast
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Weather' => 'world/weathers', 'Weather Forecast' => 'world/forecast']) !!}
    <h1>Weather Forecast</h1>


    <br>
    <h4>The weather is currently...</h4>
    @if ($weather)
        <div class="card mb-3">
            <div class="card-body">
                @include('world._weather_entry', [
                    'imageUrl' => $weather->imageUrl,
                    'name' => $weather->displayName,
                    'description' => $weather->parsed_description,
                    'searchUrl' => $weather->searchUrl,
                    'weather' => $weather,
                ])
            </div>
        </div>
    @else
        <p>Looks like we don't have any data for the weather at the moment... stay tuned!
        <p>
    @endif
    <h4>And the season is currently...</h4>
    @if ($season)
        <div class="card mb-3">
            <div class="card-body">
                @include('world._season_entry', ['name' => $season->name])
            </div>
        </div>
    @else
        <p>Looks like we don't have any data for the season at the moment... stay tuned!</p>
    @endif
@endsection
