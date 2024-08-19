<div class="row world-entry">
    @if($weather->imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $weather->imageUrl }}" data-lightbox="entry" data-title="{{ $weather->name }}"><img src="{{ $weather->imageUrl }}" class="world-entry-image" alt="{{ $weather->name }}" /></a></div>
    @endif
    <div class="{{ $weather->imageUrl ? 'col-md-9' : 'col-12' }}">
        <x-admin-edit title="Weather" :object="$weather" />
        <h3>{!! $weather->displayName !!}</h3>
        @if($weather->summary) <div class="text-muted"><i>" {!! $weather->summary !!} " </i></div>@endif
        <div class="world-entry-text">
        {!! $weather->parsed_description !!}
        </div>
    </div>
</div>
