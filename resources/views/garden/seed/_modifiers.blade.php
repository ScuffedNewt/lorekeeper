<h5 class="h5">Modifiers</h5>
<div class=" bg-light overflow-auto card" style="overflow-x: scroll;">
    <div class=" p-2 btn-group-toggle" id="modifierSelector" data-toggle="buttons">
        <label type="button" class="modifier-btn btn mr-2 my-2 modifier-button" type="radio" autocomplete="off">
            <input type="radio" name="options" autocomplete="off" value="0">
            <i class="fas fa-times fa-5x text-white"></i>
            <div class="text-small font-weight-bold text-secondary">No Modifier</div>
        </label>
        @foreach ($userModifiers as $modifier)
            @foreach ($modifier->chunk(4) as $chunk)
                @foreach ($chunk as $stack)
                    <label type="button" class="modifier-btn btn mr-2 my-2 modifier-button" style="height: 100%" type="radio" autocomplete="off">
                        <input type="radio" name="options" autocomplete="off" value="{{ $stack->first()->id }}">
                        <img class="img-fluid image-zoom" src="{{ $stack->first()->imageUrl }}" style="max-height: 6vh;">
                        <div class="text-small font-weight-bold text-secondary"> {!! $stack->first()->name !!} <br>Quantity: x{{ $stack->sum('pivot.count') }}</div>
                    </label>
                @endforeach
            @endforeach
        @endforeach
    </div>
</div>
