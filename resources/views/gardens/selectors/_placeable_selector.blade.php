@php
    $asset = getAsset($model, $id);
    $gardenObject = getGardenObjectByAsset($asset);
    $html = null;

    if ($quantity > 0) {
        $icon = $asset->has_image ? `<img src='` . $asset->imageUrl . `'>` : null;

        $html = sprintf(
            'draggable="true" data-name="%s" data-id="%s" data-current-garden-id="%s" data-asset-type="%s" data-object-type="%s" data-icon="%s"',
            htmlspecialchars($asset->name),
            $id,
            $garden->id,
            $asset->assetType,
            $gardenObjectType,
            $icon,
        );
    }
@endphp
<button class="slot seed btn" {!! $html !!}>
    <span class="quantity badge badge-pill badge-dark">x{{ $quantity }}</span>
    <div class="icon">
        @if ($asset->has_image)
            <img src="{{ $asset->imageUrl }}" style="max-height: 30px;">
        @else
            🌱
        @endif
    </div>
    <div class="name">
        {{ $asset->name }}
        @if ($gardenObject && $gardenObject->requiredQuality > 0)
            <div>Quality: {{ $gardenObject->requiredQuality }}</div>
        @endif
    </div>
</button>


{{-- {{ $stack->availableQuantity > 0 ? 'data-garden-id="' . $stack->item->id . '" data-current-garden-id="' . $garden->id . '" data-stack-type="' . $stack_type . '" data-object-type="' . $object_type . '"' : '' }}> --}}
