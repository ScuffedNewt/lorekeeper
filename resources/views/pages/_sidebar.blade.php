<ul class="text-center">
    <li class="sidebar-header"><a href="#" class="card-link">Featured Character</a></li>

    <li class="sidebar-section p-2">
        @if(isset($featuredCharacter) && $featuredCharacter)
            <div>
                <a href="{{ $featuredCharacter->url }}"><img src="{{ $featuredCharacter->image->thumbnailUrl }}" class="img-thumbnail" /></a>
            </div>
            <div class="mt-1">
                <a href="{{ $featuredCharacter->url }}" class="h5 mb-0">@if(!$featuredCharacter->is_visible) <i class="fas fa-eye-slash"></i> @endif {{ $featuredCharacter->fullName }}</a>
            </div>
            <div class="small">
                {!! $featuredCharacter->image->species_id ? $featuredCharacter->image->species->displayName : 'No Species' !!} ・ {!! $featuredCharacter->image->rarity_id ? $featuredCharacter->image->rarity->displayName : 'No Rarity' !!} ・ {!! $featuredCharacter->displayOwner !!}
            </div>
        @else
            <p>There is no featured character.</p>
        @endif
    </li>
</ul>
