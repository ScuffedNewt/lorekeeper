@php
    $asset = getAsset($model, $id);
    $gardenObject = getGardenObjectByAsset($asset);
    $html = null;

    if ($quantity > 0) {
        $icon = $asset->has_image ? `<img src='` . $asset->imageUrl . `'>` : null;

        $html = sprintf(
            'draggable="true" data-name="%s" data-id="%s" data-current-garden-id="%s" data-asset-type="%s" data-object-type="%s" data-icon="%s" data-quantity="%s"',
            htmlspecialchars($asset->name),
            $id,
            $garden->id,
            $asset->assetType,
            $gardenObjectType,
            $icon,
            $quantity,
        );
    }
@endphp
<button class="slot asset btn" {!! $html !!}>
    <span class="quantity badge badge-pill badge-dark">x{{ $quantity }}</span>
    <div class="icon">
        @if ($asset->has_image)
            <img src="{{ $asset->imageUrl }}" style="max-height: 30px;">
        @else
            <i class="fas fa-seedling" aria-hidden="true"></i>
        @endif
    </div>
    <div class="name">
        {{ $asset->name }}
        @if ($gardenObject && $gardenObject->requiredQuality > 0)
            <div>Quality: {{ $gardenObject->requiredQuality }}</div>
        @endif
        @if ($gardenObject && !$gardenObject->isInSeason())
            <div class="text-warning">Greenhouse only</div>
        @endif
    </div>
</button>
