@if ($weather)
    {!! Form::open(['url' => 'admin/weather/weathers/delete/' . $weather->id]) !!}

    <p>You are about to delete the weather <strong>{{ $weather->name }}</strong>. This is not reversible. If the site is currently in this weather, you will not be able to delete it without changing the weather first.</p>
    <p>Are you sure you want to delete <strong>{{ $weather->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Weather', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid weather selected.
@endif
