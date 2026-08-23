@extends('admin.layout')

@section('admin-title')
    Maps
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Maps' => 'admin/maps']) !!}

    <h1>Maps</h1>

    <p>This is a list of maps in the game. Use @map(id) on pages to use interactive version or $map->display in code</p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/maps/create') }}"><i class="fas fa-plus"></i> Create New Map</a>
    </div>

    @if (!count($maps))
        <p>No maps found.</p>
    @else
        <div class="row ml-md-2 mb-4">
            <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
                <div class="col-8 col-md-8 font-weight-bold">Name</div>
            </div>
            @foreach ($maps as $map)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
                    <div class="col-8 col-md-8">#{{ $map->id }}. {{ $map->name }} </div>
                    <div class="col-3 col-md-1 text-right">
                        <a href="{{ url('admin/maps/edit/' . $map->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection

@section('scripts')
    @parent
@endsection
