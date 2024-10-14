<div class="row world-entry">
    @if ($variant->has_image)
        <div class="col-md-3 world-entry-image">
            <a href="{{ $variant->imageUrl }}" data-lightbox="entry" data-title="{{ $variant->name }}">
                <img src="{{ $variant->imageUrl }}" class="world-entry-image" alt="{{ $variant->name }}" />
            </a>
        </div>
    @endif
    <div class="{{ $variant->has_image ? 'col-md-9' : 'col-12' }}">
        <x-admin-edit title="Trait" :object="$variant" />
        <h3>
            @if (!$variant->is_visible)
                <i class="fas fa-eye-slash mr-1"></i>
            @endif
            {!! $variant->displayName !!}
            <a href="{{ $variant->searchUrl }}" class="world-entry-search text-muted">
                <i class="fas fa-search"></i>
            </a>
        </h3>
        @if ($variant->feature_category_id)
            <div>
                <strong>Category:</strong> {!! $variant->category->displayName !!}
            </div>
        @endif
        @if ($variant->species_id)
            <div>
                <strong>Species:</strong> {!! $variant->species->displayName !!}
                @if ($variant->subtype_id)
                    ({!! $variant->subtype->displayName !!} subtype)
                @endif
            </div>
        @endif
        <div class="world-entry-text parsed-text">
            {!! $variant->parsed_description !!}
        </div>
    </div>
</div>
