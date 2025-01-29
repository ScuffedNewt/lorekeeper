<div class="row world-entry">
    @if ($pet->imageUrl)
        <div class="col-md-3 world-entry-image">
            <a href="{{ $pet->imageUrl }}" data-lightbox="entry" data-title="{{ $pet->name }}">
                <img src="{{ $pet->imageUrl }}" class="world-entry-image" alt="{{ $pet->name }}" />
            </a>
        </div>
    @endif
    <div class="{{ $pet->imageUrl ? 'col-md-9' : 'col-12' }}">
        <x-admin-edit title="Pet" :object="$pet" />
        <h2 class="h3">
            @if (!$pet->is_visible)
                <i class="fas fa-eye-slash mr-1"></i>
            @endif
            {!! $pet->displayName !!}
            @if ($pet->category)
                <i class="h4"> ({!! $pet->category->displayName !!})</i>
            @endif
            <a href="{{ $pet->idUrl }}" class="world-entry-search text-muted">
                <i class="fas fa-search"></i>
            </a>
        </h2>
        <div class="world-entry-text">
            {!! $pet->description !!}
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
            @if ($pet->hasDrops)
                <div class="alert alert-info">
                    This pet has drops! <a href="{{ $pet->idUrl }}">Click here to view them</a>.
                </div>
            @endif
        </div>
    </div>
</div>
