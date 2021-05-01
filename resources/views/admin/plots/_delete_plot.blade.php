@if($plot)
    {!! Form::open(['url' => 'admin/data/plots/delete/'.$plot->id]) !!}

    <p>You are about to delete the plot #<strong>{{ $plot->id }}</strong>. This is not reversible. If this plot exists in at least one user's possession, you will not be able to delete this plot.</p>
    <p>Are you sure you want to delete #<strong>{{ $plot->id }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Plot', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid plot selected.
@endif