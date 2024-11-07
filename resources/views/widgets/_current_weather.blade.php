@php
    $season = getSiteWeather()['season'];
    $weather = getSiteWeather()['weather']
@endphp

@if (Settings::get('show_weather_on_front_page') && ($season || $weather))
    <div class="card mt-3">
        <div class="card-body text-center">
            @if ($season)
                <b>Current Season:</b>
                <h5>{!! $season->displayName !!}</h5>
                @if ($season->has_image)
                    <img src="{{ $season->imageUrl }}" alt="{{ $season->name }}" class="img-thumbnail" />
                @endif
                @if ($season->summary)
                    <div class="text-muted"><i>"{!! $season->summary !!}"</i></div>
                @endif
            @endif
            <hr class="{{ $season && $weather ? '' : 'hide' }}">
            @if ($weather)
                <b>The weather is currently...</b>
                <h5>{!! $weather->displayName !!}</h5>
                @if ($weather->has_image)
                    <img src="{{ $weather->imageUrl }}" alt="{{ $weather->name }}" class="img-thumbnail" />
                @endif
                @if ($weather->summary)
                    <div class="text-muted"><i>" {!! $weather->summary !!} " </i></div>
                @endif
            @endif
        </div>
    </div>
@endif
