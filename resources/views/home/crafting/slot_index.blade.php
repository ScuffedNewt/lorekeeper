@extends('home.layout')

@section('home-title') Crafting Slots @endsection

@section('home-content')
{!! breadcrumbs(['Crafting' => 'crafting', 'Slot' => 'slot',]) !!}

<h1>
    Available Slots
</h1>

@foreach($slots as $slot)
    <div class="card text-center" style="width: 200px; height: 200px; background-color: grey;">
        <h3 class="pt-2"> Slot #{{ $slot->id }}</h3>
        @if($user->craftingslots->contains('slot_id', $slot->id))
        You own this slot already!
        @else
        This slot {{ $slot->free ? 'is free!' : 'costs' . $slot->slot_cost . ' ' . $slot->currency->name }}
        {!! Form::open(['url' => 'crafting/slots/purchase/' . $slot->id]) !!}
        {!! Form::submit('Purchase slot?', ['class' => 'btn btn-sm btn-primary mt-3']) !!}
        {!! Form::close() !!}
        @endif
    </div>
@endforeach

@endsection