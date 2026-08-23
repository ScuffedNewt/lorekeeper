@if ($map)
    {!! Form::open(['url' => 'admin/maps/delete/' . $map->id]) !!}

    <p>Are you sure you want to delete <strong>{{ $map->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Map', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid map selected.
@endif
