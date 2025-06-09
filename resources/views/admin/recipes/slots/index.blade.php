@extends('admin.layout')

@section('admin-title')
    Crafting Recipe Slots
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Crafting Recipe Slots' => 'admin/data/recipes/slots']) !!}

    <h1>Crafting Recipe Slots</h1>

    <p>Slots are used to limit the number of recipes that can be crafted at once.</p>
    <p>Each slot can have a cost associated with it, which is paid to unlock the slot OR whenever a recipe is crafted.</p>

    <div class="text-right mb-3">
        <a class="btn btn-secondary" href="{{ url('admin/data/recipes') }}"><i class="fas fa-undo"></i> Return to Recipes</a>
        <a class="btn btn-primary" href="{{ url('admin/data/recipes/slots/create') }}"><i class="fas fa-plus"></i> Create New Recipe Slot</a>
    </div>

    @if (!count($slots))
        <p>No slots found.</p>
    @else
        <div class="row ml-md-2 mb-4">
            <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
                <div class="col-2 col-md-2 font-weight-bold">Name</div>
                <div class="col-8 col-md-8 font-weight-bold">Cost</div>
            </div>
            @foreach ($slots as $slot)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
                    <div class="col-2 col-md-2"> #{{ $slot->id }} {{ $slot->name }} </div>
                    <div class="col-8 col-md-8 text-center">
                        @include('widgets._limits', [
                            'object' => $slot,
                            'compact' => true,
                            'hideUnlock' => true,
                            'showNoLimits' => true,
                        ])
                    </div>
                    <div class="col-3 col-md-2 text-right">
                        <a href="{{ url('admin/data/recipes/slots/edit/' . $slot->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection

@section('scripts')
    @parent
@endsection
