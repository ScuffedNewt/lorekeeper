<div class="text-center card" style="overflow-y: scroll; overflow-x: hidden; flex 0 0 7em; min-width: 11em; max-width:11em;">
    <h5 class="text-left">{{ $plots->first()->itemsTitles}}</h5>
    <div class=" p-2 btn-group-vertical btn-group-toggle" id="{{ $plots->first()->plot_type.'Selector' }}" data-toggle="buttons">
        <label type="button" class="btn  my-1 btn-block {{ $plots->first()->plot_type == 'seed' ? 'seed' : 'animal' }}-button" type="radio" autocomplete="off" >
            <input type="radio" name="options" autocomplete="off" value="0" >
                <i class="fas fa-times fa-5x text-white"></i>
                <div class="text-small text-white text-center" style="font-size:1rem;">No {{ $plots->first()->plot_type == 'seed' ? 'Seeds' : 'Animals' }}</div>
        </label>
        @foreach($userItems as $item)
            @foreach($item->chunk(4) as $chunk)
                @foreach($chunk as $stack)
                @if($stack->sum('pivot.count') - $stack->sum('pivot.garden_count') > 0)
                    <label type="button" class="btn my-1 btn-block {{ $plots->first()->plot_type == 'seed' ? 'seed' : 'animal' }}-button" type="radio" autocomplete="off">
                        <input type="radio" name="options" autocomplete="off" value="{{$stack->first()->id}}" >
                            <img class="img-fluid image-zoom"  src="{{ $stack->first()->imageUrl }}" style="width: 50%">
                        <div class="text-small text-white text-center mt-2 text-wrap" style="font-size:1rem;">
                            {!!$stack->first()->name !!}
                            <small><small>
                                <br>Quantity: x{{ $stack->sum('pivot.count') - $stack->sum('pivot.garden_count')}}
                                @if($plot->first()->plot_type == 'seed' && $stack->first()->tags->where('tag', 'seed')->first() && $stack->first()->tags->where('tag', 'seed')->first()->data['quality'])
                                    <br>Quality: {{ $stack->first()->tags->where('tag', 'seed')->first()->data['quality'] }}
                                @endif
                            </small></small>
                        </div>
                    </label>
                @else
                    <div class="btn my-1 btn-block {{ $plots->first()->plot_type == 'seed' ? 'seed' : 'animal' }}-button disabled">
                        <img class="img-fluid image-zoom"  src="{{ $stack->first()->imageUrl }}" style="width: 50%">
                        <div class="text-small text-white text-center mt-2 text-wrap" style="font-size:1rem;">
                            {!!$stack->first()->name !!}
                            <small><small>
                                <br>Quantity: x{{ $stack->sum('pivot.count') - $stack->sum('pivot.garden_count')}}
                                @if($plot->first()->plot_type == 'seed' && $stack->first()->tags->where('tag', 'seed')->first() && $stack->first()->tags->where('tag', 'seed')->first()->data['quality'])
                                    <br>Quality: {{ $stack->first()->tags->where('tag', 'seed')->first()->data['quality'] }}
                                @endif
                            </small></small>
                        </div>
                    </div>
                @endif
                @endforeach
            @endforeach
        @endforeach
    </div>
</div>
