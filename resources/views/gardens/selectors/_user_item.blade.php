@if ($stack->getAvailableQuantityAttribute() > 0)
    <label type="button" class="btn my-1 btn-block {{ $plots->first()->plot_type == 'seed' ? 'seed' : 'animal' }}-button" type="radio" autocomplete="off">
        <input type="radio" name="options" autocomplete="off" value="{{ $stack->id }}">
        @if ($stack->item->has_image)
            <img class="image-zoom w-50" src="{{ $stack->item->imageUrl }}">
        @endif
        <div class="text-small text-white text-center mt-2 text-wrap" style="font-size:1rem;">
            {!! $stack->item->displayName !!}
            <small><small>
                <br>Quantity: x{{ $stack->getAvailableQuantityAttribute() }} ({{ $stack->getOthers() }})
            </small></small>
        </div>
    </label>
@else
    <div class="btn my-1 btn-block {{ $plots->first()->plot_type == 'seed' ? 'seed' : 'animal' }}-button disabled">
        <img class="image-zoom w-50" src="{{ $stack->item->imageUrl }}">
        <div class="text-small text-white text-center mt-2 text-wrap" style="font-size:1rem;">
            {!! $stack->first()->name !!}
            <small><small>
                    <br>Quantity: x{{ $stack->sum('pivot.count') - $stack->sum('pivot.garden_count') }}
                    @if (
                        $plot->first()->plot_type == 'seed' &&
                            $stack->first()->tags->where('tag', 'seed')->first() &&
                            $stack->first()->tags->where('tag', 'seed')->first()->data['quality']
                    )
                        <br>Quality: {{ $stack->first()->tags->where('tag', 'seed')->first()->data['quality'] }}
                    @endif
                </small></small>
        </div>
    </div>
@endif
