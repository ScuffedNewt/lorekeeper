@php
    $objectWeather = getObjectWeather($object);
@endphp

@if ($objectWeather && count($objectWeather->weathers))
    @if (!$objectWeather->is_hidden)
        <div class="alert alert-info">
            <h4>Weather</h4>
            <p class="mb-0">Current Weather: {!! $objectWeather->getActiveWeatherMessage() !!}</p>
        </div>
    @else
        <div class="alert alert-info">
            <p class="mb-0">This {{ $objectWeather->object->assetType }} has revealed itself because of the {!! getSiteWeather()['weather']->displayName !!} weather!</p>
        </div>
    @endif
@endif
