<div class="row world-entry">
    <div class="col-md-3 world-entry-image"><a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}"><img src="{{ $imageUrl }}" class="world-entry-image" alt="{{ $name }}" /></a></div>
    <div class="col-md-9">
        <h3>{!! $name !!} @if (isset($url) && $url)
                <a href="{{ $url }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a>
            @endif
        </h3>
        <div class="row">
            <div class="col-md-6 col-md">
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
            {!! $description !!}
        </div>
    </div>
</div>
