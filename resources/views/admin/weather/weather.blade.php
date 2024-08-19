@extends('admin.layout')

@section('admin-title')
    Weather
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Weather' => 'admin/weather/weathers']) !!}

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/weather/seasons') }}">
            <i class="fas fa-cloud-sun"></i> Seasons
        </a>
    </div>

    <h1>Weather</h1>

    <p>
        Weather are the different types of weather that can be rolled on during the game. This will roll a random weather from the contents of a particular active season.
    </p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/weather/weathers/create') }}"><i class="fas fa-plus"></i> Create New Weather</a>
    </div>
    @if (!count($weathers))
        <p>No weathers found.</p>
    @else
        {!! $weathers->render() !!}
        <div class="row ml-md-2">
            <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
                <div class="col-3 col-md-2 font-weight-bold">Name</div>
                <div class="col-3 col-md-4 font-weight-bold">Visible?</div>
                <div class="col-6 col-md-5 font-weight-bold">Current Weather?</div>
            </div>
            @foreach ($weathers as $weather)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                    <div class="col-3 col-md-2 ">{{ $weather->name }} (#{{ $weather->id }})</div>
                    <div class="col-3 col-md-4">{{ $weather->is_visible ? 'Visible' : ' ' }}</div>
                    <div class="col-3 col-md-5">
                        @if (Settings::get('site_weather') == $weather->id)
                            <i class="text-success fas fa-check"></i>
                        @endif
                    </div>
                    <div class="col-3 col-md-1 text-right"><a href="{{ url('admin/weather/weathers/edit/' . $weather->id) }}" class="btn btn-primary py-0 px-2">Edit</a></div>
                </div>
            @endforeach
        </div>
        {!! $weathers->render() !!}
        <div class="text-center mt-4 small text-muted">{{ $weathers->total() }} result{{ $weathers->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection
