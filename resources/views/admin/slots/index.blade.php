@extends('admin.layout')

@section('admin-title')
    Slots
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Slots' => 'admin/data/slots']) !!}

    <h1>Slots</h1>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/slots/create') }}"><i class="fas fa-plus"></i> Create New Slot</a>
    </div>

    @if (!count($slots))
        <p>No slots found.</p>
    @else
        <div class="row ml-md-2 mb-4">
            <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
                <div class="col-5 col-md-6 font-weight-bold">Slot #</div>
                <div class="col-5 col-md-5 font-weight-bold">Cost</div>
            </div>
            @foreach ($slots as $slot)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
                    <div class="col-5 col-md-6"> {{ $slot->id }} </div>
                    <div class="col-4 col-md-5"> {{ $slot->slot_cost ? $slot->slot_cost . ' ' . $slot->currency->name : 'Free' }} </div>
                    <div class="col-3 col-md-1 text-right">
                        <a href="{{ url('admin/data/slots/edit/' . $slot->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection

@section('scripts')
    @parent
@endsection
