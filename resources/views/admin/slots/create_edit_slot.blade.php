@extends('admin.layout')

@section('admin-title') Slots @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Slots' => 'admin/data/slots', ($slot->id ? 'Edit' : 'Create').' Slot' => $slot->id ? 'admin/data/slots/edit/'.$slot->id : 'admin/data/slots/create']) !!}

<h1>{{ $slot->id ? 'Edit' : 'Create' }} Slot
    @if($slot->id)
        <a href="#" class="btn btn-outline-danger float-right delete-slot-button">Delete Slot</a>
    @endif
</h1>

{!! Form::open(['url' => $slot->id ? 'admin/data/slots/edit/'.$slot->id : 'admin/data/slots/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="row">
    <div class="col-md form-group">
        {!! Form::checkbox('free', 1, $slot->id ? $slot->free : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('free', 'Should this slot be free?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will need to pay to unlock the slot.') !!}
    </div>
</div>

<div class="form-group">
    <p> Leave empty if the slot is going to be free </p>
    {!! Form::label('slot_cost') !!}
    {!! Form::number('slot_cost', $slot->slot_cost, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    <p> Leave empty if the slot is going to be free </p>
    {!! Form::label('currency_id') !!}
    {!! Form::select('currency_id', $currencies, null, ['class' => 'form-control']) !!}
</div>

<div class="text-right">
    {!! Form::submit($slot->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.selectize').selectize();

    $('.delete-slot-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/slots/delete') }}/{{ $slot->id }}", 'Delete Slot');
    });
});

</script>
@endsection
