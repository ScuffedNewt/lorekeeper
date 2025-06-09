@if (!$recipe)
    <div class="text-center">
        Invalid recipe selected.
    </div>
@else
    @if ($recipe->imageUrl)
        <div class="text-center">
            <div class="mb-3">
                <img class="recipe-image" src="{{ $recipe->imageUrl }}" />
            </div>
        </div>
    @endif
    <h3>
        Recipe Details
        <a class="small inventory-collapse-toggle collapse-toggle" href="#recipeDetails" data-toggle="collapse">
            Show
        </a>
    </h3>
    <hr>
    <div class="collapse show" id="recipeDetails">
        @if (hasLimits($recipe))
            @include('widgets._limits', [
                'object' => $recipe,
            ])
            <hr />
        @endif
        <div class="row">
            <div class="col-md-6">
                <h5>Ingredients</h5>
                @foreach ($recipe->ingredients as $ingredient)
                    <div class="alert alert-secondary">
                        @include('home.crafting._recipe_ingredient_entry', [
                            'ingredient' => $ingredient,
                        ])
                    </div>
                @endforeach
            </div>
            <div class="col-md-6">
                <h5>Rewards</h5>
                @foreach (parseAssetData($recipe->output) as $type)
                    @foreach ($type as $item)
                        <div class="alert alert-secondary">
                            @include('home.crafting._recipe_reward_entry', [
                                'reward' => $item,
                            ])
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
    @if ($selected || $recipe->onlyCurrency)
        {!! Form::open(['url' => 'crafting/craft/' . $recipe->id]) !!}
        @if ($recipe->time)
            @if (!$slots->count())
                <p class="alert alert-danger">This recipe requires time to craft! However, you do not have any slots available right now.</p>
            @else
                <p class="alert alert-info">This recipe requires time to craft! Please select the slot # you'd like to use.</p>
                {!! Form::select('slot_id', $slots, null, ['class' => 'form-control mb-2']) !!}
                @include('widgets._inventory_select', [
                    'user' => Auth::user(),
                    'inventory' => $inventory,
                    'categories' => $categories,
                    'selected' => $selected,
                    'page' => $page,
                ])
                <div class="text-right">
                    {!! Form::submit('Craft', ['class' => 'btn btn-primary']) !!}
                </div>
                {!! Form::close() !!}
            @endif
        @else
            @include('widgets._inventory_select', [
                'user' => Auth::user(),
                'inventory' => $inventory,
                'categories' => $categories,
                'selected' => $selected,
                'page' => $page,
            ])
            <div class="text-right">
                {!! Form::submit('Craft', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        @endif
    @else
        <div class="alert alert-danger">You do not have all of the required recipe ingredients.</div>
    @endif
@endif

@include('widgets._inventory_select_js')
<script>
    $(document).keydown(function(e) {
        var code = e.keyCode || e.which;
        if (code == 13)
            return false;
    });
</script>
