@if ($plot)
    {!! Form::open(['url' => 'admin/gardens/edit/'.$plot->garden->id.'/plots/delete/'.$plot->id]) !!}

    <p>
        You are about to delete this plot belonging to garden <strong>{{ $plot->garden->name }}</strong>. This is not reversible.
        If this plot exists in at least one user's possession, you will not be able to delete this plot.
    </p>
    <p>Are you sure you want to delete plot #<strong>{{ $plot->id }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Plot', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid plot selected.
@endif
