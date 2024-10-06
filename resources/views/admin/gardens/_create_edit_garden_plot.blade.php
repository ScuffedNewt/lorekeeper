{!! Form::open(['url' => 'admin/gardens/edit/'.$garden->id.'/plots/'. ($plot->id ? 'edit/'.$plot->id : 'create')]) !!}

@if (!$plot->id)
    <div class="alert alert-info">
        <strong>Heads up!</strong> You are creating a new plot for the garden <strong>"{!! $garden->displayName !!}"</strong>.
        <br/>
        Extra information can be added to the plot after it has been created.
    </div>
@else
    
@endif

<div class="text-right">
    {!! Form::submit($plot->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}