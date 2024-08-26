<div class="row world-entry">
    @if ($feature->has_image)
        <div class="col-md-3 world-entry-image">
            <a href="{{ $feature->imageUrl }}" data-lightbox="entry" data-title="{{ $feature->name }}">
                <img src="{{ $feature->imageUrl }}" class="world-entry-image" alt="{{ $feature->name }}" />
            </a>
        </div>
    @endif
    <div class="{{ $feature->has_image ? 'col-md-9' : 'col-12' }}">
        <x-admin-edit title="Trait" :object="$feature" />
        <h3>
            @if (!$feature->is_visible)
                <i class="fas fa-eye-slash mr-1"></i>
            @endif
            {!! $feature->displayName !!}
            <a href="{{ $feature->searchUrl }}" class="world-entry-search text-muted">
                <i class="fas fa-search"></i>
            </a>
        </h3>
        @if ($feature->feature_category_id)
            <div>
                <strong>Category:</strong> {!! $feature->category->displayName !!}
            </div>
        @endif
        @if ($feature->species_id)
            <div>
                <strong>Species:</strong> {!! $feature->species->displayName !!}
                @if ($feature->subtype_id)
                    ({!! $feature->subtype->displayName !!} subtype)
                @endif
            </div>
        @endif
        <div class="world-entry-text parsed-text">
            {!! $feature->parsed_description !!}
            @if (count($feature->alternative_rarities ?? []))
                <hr />
                <h5>Alternative Rarities</h5>
                <div class="row">
                    @foreach ($feature->displayAlternativeRarities() ?? [] as $data)
                        <div class="col-md-3">
                            @if ($data['subtype'])
                                <div>
                                    <strong>{!! $data['subtype']->displayName  !!} ({!! $data['species']->displayName !!} Subtype):</strong> {!! $data['rarity']->displayName !!}
                                </div>
                            @else
                                <div>
                                    <strong>{!! $data['species']->displayName !!}:</strong> {!! $data['rarity']->displayName !!}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
