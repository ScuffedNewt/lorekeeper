@php
    $objectWeather = \App\Models\Weather\ObjectWeather::where('object_id', $object->id)
        ->where('object_model', get_class($object))
        ->first();
@endphp

@if ($objectWeather && count($objectWeather->weathers))
    <div class="alert alert-info">
        <h4>Weather</h4>
        <p class="mb-0">Current Weather: {!! $objectWeather->getActiveWeatherMessage() !!}</p>
    </div>
@endif
