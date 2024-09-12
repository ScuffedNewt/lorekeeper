<div class="row world-entry">
    @if ($prompt->has_image)
        <div class="col-md-3 world-entry-image">
            <img src="{{ $prompt->image_url }}" class="img-fluid" alt="{{ $prompt->name }}" style="filter: grayscale(100%);" />
        </div>
    @endif
    <div class="{{ $prompt->has_image ? 'col-md-9' : 'col-12' }}">
        <div class="mb-3">
            <h3 class="mb-0">{!! $prompt->name !!}</h3>
            @if ($prompt->prompt_category_id)
                <div><strong>Category: </strong>{!! $prompt->category->displayName !!}</div>
            @endif
            @if ($prompt->start_at && $prompt->start_at->isFuture())
                <div><strong>Starts: </strong>{!! format_date($prompt->start_at) !!} ({{ $prompt->start_at->diffForHumans() }})</div>
            @endif
            @if ($prompt->end_at)
                <div><strong>Ends: </strong>{!! format_date($prompt->end_at) !!} ({{ $prompt->end_at->diffForHumans() }})</div>
            @endif
        </div>
        <div class="world-entry-text">
            <h6 class="text-danger">
                This prompt requires you to have completed {!! $prompt->parent->displayName !!} {{ $prompt->parent_quantity }} {{ $prompt->parent_quantity > 1 ? 'times' : 'time' }}.
            </h6>
            <p>You cannot view any details until you have completed the prerequisite.</p>
        </div>
    </div>
</div>
