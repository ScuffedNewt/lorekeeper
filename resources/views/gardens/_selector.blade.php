
<div class="text-center card" style="overflow-y: scroll;">
    <div class=" p-2 btn-group-vertical btn-group-toggle" id="{{ $plots->first()->plot_type . 'Selector' }}" data-toggle="buttons">
        <label type="button" class="btn  my-1 btn-block seed-button" type="radio" autocomplete="off">
            <input type="radio" name="options" autocomplete="off" value="0">
            <i class="fas fa-times fa-5x text-white"></i>
            <div class="text-small text-white text-center" style="font-size:1rem;">Deselect</div>
        </label>
        @foreach ($user_garden_objects as $type=>$objects)
            @foreach ($objects->chunk(4) as $chunk)
                @foreach ($chunk as $stack)
                    <label type="button" class="btn my-1 btn-block seed-button" type="radio" autocomplete="off">
                        <input type="radio" name="options" autocomplete="off" value="{{ $stack->id }}">
                            {{-- Add more types here --}}
                            @if (get_class($stack) == 'App\Models\User\UserItem')
                                @include('gardens.selectors._user_item', ['stack' => $stack])
                            @endif
                    </label>
                @endforeach
            @endforeach
        @endforeach
    </div>
</div>
