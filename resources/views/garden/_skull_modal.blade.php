@if($plot)
    {!! Form::open(['url' => 'garden/destroy/'.$plot->id]) !!}

    <p>You are about to destroy the crop <strong>{{ $plot->item->name }}</strong> in plot #{{ $plot->plot_id }}. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>{{ $plot->item->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Destroy Crop', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid plot selected.
@endif