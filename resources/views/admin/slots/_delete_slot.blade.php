@if($slot)
    {!! Form::open(['url' => 'admin/data/slots/delete/'.$slot->id]) !!}

    <p>You are about to delete the slot #<strong>{{ $slot->id }}</strong>. This is not reversible. If this slot exists in at least one user's possession, you will not be able to delete this slot.</p>
    <p>Are you sure you want to delete #<strong>{{ $slot->id }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Slot', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid slot selected.
@endif