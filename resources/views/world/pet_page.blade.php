@extends('world.layout')

@section('title')
    {{ $pet->name }}
@endsection

@section('meta-img')
    {{ $pet->imageUrl }}
@endsection

@section('meta-desc')
    {!! substr(str_replace('"', '&#39;', $pet->description), 0, 69) !!}
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Pets' => 'world/pets', $pet->name => 'world/pets/' . $pet->id]) !!}

    <x-admin-edit title="Pet" :object="$pet" />

    <div class="row">
        <div class="col-sm">
        </div>
        <div class="col-lg-6 col-lg-10">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row world-entry {{ $pet->evolutions->count() || $pet->variants->count() ? 'mb-3' : '' }}">
                        @if ($pet->has_image)
                            <div class="col-md-3 world-entry-image">
                                <a href="{{ $pet->imageUrl }}" data-lightbox="entry" data-title="{{ $pet->name }}">
                                    <img src="{{ $pet->imageUrl }}" class="world-entry-image" alt="{{ $pet->name }}" />
                                </a>
                            </div>
                        @endif
                        <div class="{{ $pet->has_image ? 'col-md-9' : 'col-12' }}">
                            <h1>
                                @if (!$pet->is_visible)
                                    <i class="fas fa-eye-slash mr-1"></i>
                                @endif
                                {!! $pet->name !!}
                            </h1>
                            <div class="row">
                                @if (isset($pet->category) && $pet->category)
                                    <div class="col-md">
                                        <p>
                                            <strong>Category:</strong>
                                            <a href="{!! $pet->category->url !!}">
                                                {!! $pet->category->name !!}
                                            </a>
                                        </p>
                                    </div>
                                @endif
                            </div>
                            <div class="world-entry-text">
                                {!! $pet->description !!}
                            </div>
                            @if ($pet->hasDrops)
                                <div class="card">
                                    <h5 class="card-header inventory-header">
                                        <a class="inventory-collapse-toggle collapse-toggle collapsed" href="#drop-collapse" data-toggle="collapse">Show Drops</a></h3>
                                    </h5>
                                    <div class="collapse" id="drop-collapse">
                                        <div class="card-body">
                                            @include('world._pet_drop_entry', ['pet' => $pet])
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @if ($pet->evolutions->count())
                        <div class="card mb-3">
                            <div class="card-header h2">Evolutions</div>
                            <div class="card-body">
                                @foreach ($pet->evolutions->sortBy('evolution_stage')->chunk(4) as $chunk)
                                    <div class="row">
                                        @foreach ($chunk as $evolution)
                                            <div class="col text-center">
                                                <a href="{{ $evolution->imageUrl }}" data-lightbox="entry" data-title="{{ $evolution->evolution_name }}">
                                                    <img src="{{ $evolution->imageUrl }}" class="img-fluid" style="max-height: 10em;" alt="{{ $evolution->evolution_name }}" data-toggle="tooltip" data-title="{{ $evolution->evolution_name }}"
                                                        style="max-height:200px" />
                                                </a>
                                                <div class="h5">
                                                    {{ $evolution->evolution_name }} (Stage {{ $evolution->evolution_stage }})
                                                    @if (!$loop->last)
                                                        <i class="fas fa-arrow-right fa-lg mt-2"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if ($pet->variants->count())
                        <div class="card mb-3">
                            <div class="card-header h2">Variants</div>
                            <div class="card-body">
                                @foreach ($pet->variants->chunk(4) as $chunk)
                                    <div class="row">
                                        @foreach ($chunk as $variant)
                                            <div class="col-md text-center">
                                                <a href="{{ $variant->idUrl }}">
                                                    @if ($variant->has_image)
                                                        <img src="{{ $variant->imageUrl }}" class="img-fluid" style="max-height: 10em;" alt="{{ $variant->name }}" data-toggle="tooltip" data-title="{{ $variant->name }}" style="max-height:200px" />
                                                    @else
                                                        {{ $variant->name }}
                                                    @endif
                                                    <p class="mb-0">{{ $variant->description }}</p>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm">
        </div>
    </div>
@endsection
