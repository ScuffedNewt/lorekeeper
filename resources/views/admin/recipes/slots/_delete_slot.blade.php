@if ($slot)
    {!! Form::open(['url' => 'admin/data/recipes/slots/delete/' . $slot->id]) !!}

    <p>You are about to delete the slot #<strong>{{ $slot->id }} ({{ $slot->name }})</strong>. This is not reversible. If this slot exists in at least one user's possession, you will not be able to delete this slot.</p>
    <p>Are you sure you want to delete #<strong>{{ $slot->id }} ({{ $slot->name }})</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Slot', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid slot selected.
@endif
