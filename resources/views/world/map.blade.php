@extends('world.layout')

@section('title')
    {{ $map->name }}
@endsection

@section('meta-img')
    {{ $map->imageUrl }}
@endsection

@section('meta-desc')
    @if (isset($map->description))
        <p>{{ strip_tags($map->description) }}</p>
    @endif
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Maps' => 'world/maps', $map->name => 'world/maps/' . $map->name]) !!}

    <div class="row">
        <div class="col-sm">
        </div>
        <div class="col-lg-6 col-lg-10">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row world-entry">
                        <div class="col-md-3 world-entry-image"><a href="{{ $map->imageUrl }}" data-lightbox="entry" data-title="{{ $map->name }}"><img src="{{ $map->imageUrl }}" class="world-entry-image" alt="{{ $map->name }}" /></a></div>
                        <div class="col-md-9">
                            <h1>{!! $map->name !!}</h1>
                            <div class="row">
                                <div class="col-md-5 col-md">
                                    <h4>Locations</h4>
                                    <div class="row">
                                        @foreach ($map->locations as $l)
                                            @if ($l->is_active)
                                                <div class="col">
                                                    {!! $l->name !!}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="world-entry-text">
                                {!! $map->description !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm">
        </div>
    </div>
@endsection
