@php
    // garden_count for items that aren't consumed on use
    $heldString = $stack->getHeldString($stack->trade_count, $stack->update_count, $stack->submission_count, $stack->garden_count);
@endphp
@if ($stack->availableQuantity > 0)
    <div class="btn my-1 btn-block garden-button" data-garden-id="{{ $stack->item->id }}" data-current-garden-id="{{ $garden->id }}" data-stack-type="{{ $stack_type }}" data-object-type="{{ $object_type }}">
        @if ($stack->item->has_image)
            <img class="image-zoom w-50" src="{{ $stack->item->imageUrl }}">
        @endif
        <div class="text-small text-white text-center mt-2 text-wrap" style="font-size:1rem;">
            {!! $stack->item->displayName !!}
            <small>
                <small>
                    <br>Quantity: x{{ $stack->availableQuantity }} {{ $heldString ? '(' . $heldString . ')' : '' }}
                </small>
            </small>
        </div>
    </div>
@else
    <div class="btn my-1 btn-block disabled">
        @if ($stack->item->has_image)
            <img class="image-zoom w-50" src="{{ $stack->item->imageUrl }}">
        @endif
        <div class="text-small text-white text-center mt-2 text-wrap" style="font-size:1rem;">
            {!! $stack->item->name !!}
            <small>
                <small>
                    <br>Quantity: x{{ $stack->availableQuantity }} {{ $heldString ? '(' . $heldString . ')' : '' }}
                </small>
            </small>
        </div>
    </div>
@endif
