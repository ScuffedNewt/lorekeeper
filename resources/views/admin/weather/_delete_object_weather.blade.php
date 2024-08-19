@if ($objectWeather)
    {!! Form::open(['url' => 'admin/weather/objects/delete/' . $objectWeather->id]) !!}

    <p>You are about to delete the weather for <strong>{!! $objectWeather->object->displayName !!}</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>{!! $objectWeather->object->displayName !!}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Object Weather', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid object weather selected.
@endif