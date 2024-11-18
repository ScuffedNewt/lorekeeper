@php
    // garden_count for items that aren't consumed on use
$total_quantity = $stacks->sum('count') - $stacks->sum('garden_count') - $stacks->sum('trade_count') - $stacks->sum('update_count') - $stacks->sum('submission_count');
$stack = $stacks->first();
$heldString = $stack->getHeldString($stacks->sum('trade_count'), $stacks->sum('update_count'), $stacks->sum('submission_count'), $stacks->sum('garden_count'));
@endphp
@if ($total_quantity > 0)
    <div class="btn my-1 btn-block garden-button" data-garden-id="{{ $stack->id }}" data-current-garden-id="{{ $garden->id }}" data-stack-type="{{ $stack_type }}" data-object-type="{{ $object_type }}">
        @if ($stack->item->has_image)
            <img class="image-zoom w-50" src="{{ $stack->item->imageUrl }}">
        @endif
        <div class="text-small text-white text-center mt-2 text-wrap" style="font-size:1rem;">
            {!! $stack->item->displayName !!}
            <small><small>
                    <br>Quantity: x{{ $total_quantity }} {{ $heldString ? '(' . $heldString . ')' : '' }}
                </small></small>
        </div>
    </div>
@else
    <div class="btn my-1 btn-block disabled">
        @if ($stack->item->has_image)
            <img class="image-zoom w-50" src="{{ $stack->item->imageUrl }}">
        @endif
        <div class="text-small text-white text-center mt-2 text-wrap" style="font-size:1rem;">
            {!! $stack->item->name !!}
            <small><small>
                    <br>Quantity: x{{ $total_quantity }} {{ $heldString ? '(' . $heldString . ')' : '' }}
                </small></small>
        </div>
    </div>
@endif
