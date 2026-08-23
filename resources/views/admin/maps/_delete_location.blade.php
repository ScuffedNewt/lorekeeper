@if ($location)
    {!! Form::open(['url' => 'admin/maps/locations/delete/' . $location->id]) !!}

    <p>Are you sure you want to delete <strong>{{ $location->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Map', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid map selected.
@endif
