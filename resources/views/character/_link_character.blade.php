<div class="text-center">
    <div>
        <a href="{{ $character->url }}"><img src="{{ $character->image->thumbnailUrl }}" class="img-thumbnail" /></a>
    </div>
    <div class="mt-1">
        <a href="{{ $character->url }}" class="h5 mb-0">
            @if (!$character->is_visible)
                <i class="fas fa-eye-slash"></i>
            @endif {{ $character->fullName }}
        </a>
    </div>
    <div class="small">
        {!! $character->displayOwner !!}
    </div>
</div>
