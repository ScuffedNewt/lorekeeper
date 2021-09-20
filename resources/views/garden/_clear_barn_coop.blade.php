@if($plot)
    {!! Form::open(['url' => 'garden/destroy/'.$plot->id]) !!}

    <p>You are about to remove <strong>{{ $plot->item->name }}</strong> from plot #{{ $plot->plot_id }}. Your feedings will be reset and any pending rewards void.</p>
    <p>Are you sure you want to remove <strong>{{ $plot->item->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Remove Animal', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid plot selected.
@endif