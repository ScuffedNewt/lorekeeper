@php
    if (!isset($flat)) {
        $flat = false;
    }
    // garden_count for items that aren't consumed on use
    $heldString = $stack->getHeldString($stack->trade_count, $stack->update_count, $stack->submission_count, $stack->garden_count);
    $gardenObject = getGardenObjectByAsset($stack->item);
@endphp

<div class="garden-tile">
<div class="btn {{ $stack->availableQuantity > 0 ? 'garden-button' : 'disabled-button' }} btn-block {{ $flat ? 'mx-1' : 'my-1' }}"
    {{ $stack->availableQuantity > 0 ? 'data-garden-id="' . $stack->item->id . '" data-current-garden-id="' . $garden->id . '" data-stack-type="' . $stack_type . '" data-object-type="' . $object_type . '"' : '' }}>
    @if ($stack->item->has_image)
        <img class="image-zoom w-50" src="{{ $stack->item->imageUrl }}" data-toggle="tooltip" title="{{ $stack->item->name }}">
    @endif
    <div class="text-small text-white text-center mt-2 text-wrap" style="font-size:1rem;">
        @if (!$stack->item->has_image)
            {!! $stack->item->name !!}
        @endif
        <small>
            <small>
                <div>Quantity: x{{ $stack->availableQuantity }} {{ $heldString ? '(' . $heldString . ')' : '' }}</div>
                @if ($gardenObject && $gardenObject->requiredQuality > 0)
                    <div>Quality: {{ $gardenObject->requiredQuality }}</div>
                @endif
            </small>
        </small>
    </div>
</div>
</div>