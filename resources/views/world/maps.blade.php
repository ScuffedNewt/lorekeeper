@extends('world.layout')

@section('title')
    Maps
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Maps' => 'world/maps']) !!}
    <h1>Maps</h1>

    <p>Maps are here for record keeping only. Their interactive states must be used by admins on pages or other similar locations.</p>

    @foreach ($maps as $map)
        <div class="card mb-3">
            <div class="card-body">
                @include('world._map_entry', ['imageUrl' => $map->imageUrl, 'name' => $map->name, 'description' => $map->description, 'url' => $map->url])
            </div>
        </div>
    @endforeach

    <div class="text-center mt-4 small text-muted">{{ count($maps) }} result{{ count($maps) == 1 ? '' : 's' }} found.</div>
@endsection
