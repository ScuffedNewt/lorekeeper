@if ($garden)
    {!! Form::open(['url' => 'admin/gardens/delete/' . $garden->id]) !!}

    <p>You are about to delete the garden <strong>{{ $garden->name }}</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>{{ $garden->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Garden', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid garden selected.
@endif
