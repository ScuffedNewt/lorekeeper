@extends('world.layout')

@section('title')
    Weather Forecast
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Weather' => 'world/weathers', 'Weather Forecast' => 'world/forecast']) !!}

    <div class="card">
        <div class="card-header text-center h1">
            Weather Forecast
        </div>
        <div class="card-body">
            <div class="row justify-content-center mb-3">
                <div class="col-md-6 col-sm-12">
                    <div class="card text-center">
                        <div class="card-header h4">
                            Current Season
                        </div>
                        <div class="card-body">
                            @if (getSiteWeather()['season'])
                                <div class="card mb-3">
                                    <div class="card-body">
                                        @include('world._season_entry', [
                                            'season' => getSiteWeather()['season'],
                                        ])
                                    </div>
                                </div>
                            @else
                                <p>Looks like we don't have any data for the season at the moment... stay tuned!</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @if (getSiteWeather()['weather'])
                <div class="card mb-3">
                    <div class="card-body">
                        @include('world._weather_entry', [
                            'weather' => getSiteWeather()['weather'],
                        ])
                    </div>
                </div>
            @else
                <p>Looks like we don't have any data for the weather at the moment... stay tuned!</p>
            @endif
        </div>
    </div>

@endsection
