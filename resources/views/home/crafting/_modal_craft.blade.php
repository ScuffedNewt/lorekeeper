@if (!$recipe)
    <div class="text-center">
        Invalid recipe selected.
    </div>
@else
    <div class="row px-lg-3 justify-content-end">
        {{-- it's in a row div so it stops weding itself onto the right of the recipe details box when the recipe lacks an image --}}
        <x-admin-edit title="Recipe" :object="$recipe" />
    </div>
    @if ($recipe->imageUrl)
        <div class="text-center">
            <div class="mb-3">
                <img class="recipe-image" src="{{ $recipe->imageUrl }}" />
            </div>
        </div>
    @endif
    <div class="card">
        <div class="h3 card-header">
            Recipe Details
            <a class="small inventory-collapse-toggle collapse-toggle" href="#recipeDetails" data-toggle="collapse">
                Show
            </a>
        </div>
        <div class="collapse show" id="recipeDetails">
            <div class="card-body pb-0 row no-gutters">
                @if ($recipe->is_choice)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> This recipe is a choice recipe, meaning you must select which specific reward you would like to receive from the list of possible rewards.
                    </div>
                @endif

                @if (hasLimits($recipe))
                    <div class="col-12 mb-2">
                        @include('widgets._limits', [
                            'object' => $recipe,
                            'hideUnlock' => true,
                        ])
                        <hr />
                    </div>
                @endif
                <div class="col-md-6 pr-md-1">
                    <h5 class="mb-0">Ingredients</h5>
                    @foreach ($recipe->ingredients as $ingredient)
                        <div class="alert alert-secondary mb-1">
                            @include('home.crafting._recipe_ingredient_entry', [
                                'ingredient' => $ingredient,
                            ])
                        </div>
                    @endforeach
                </div>
                <div class="col-md-6 pl-md-1">
                    <h5 class="mb-0">{{ $recipe->is_choice ? 'Reward Choices' : 'Rewards' }}</h5>
                    @foreach (parseAssetData($recipe->output) as $type)
                        @foreach ($type as $item)
                            <div class="alert alert-secondary mb-1">
                                @include('home.crafting._recipe_reward_entry', [
                                    'reward' => $item,
                                ])
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card-body">
            @if ($recipe->checkRecipe(Auth::user()))
                <hr>
                {!! Form::open(['url' => 'crafting/craft/' . $recipe->id]) !!}
                @if ($recipe->time)
                    @if (!$slots->count())
                        <p class="alert alert-danger">
                            This recipe requires time to craft! However, you do not have any slots available right now.
                        </p>
                    @else
                        <p class="alert alert-info">
                            This recipe requires time to craft! Please select the slot # you'd like to use.
                        </p>
                        {!! Form::select('slot_id', $slots, null, ['class' => 'form-control mb-2']) !!}
                        @include('widgets._inventory_select', [
                            'user' => Auth::user(),
                            'inventory' => $inventory,
                            'categories' => $categories,
                            'selected' => $selected,
                            'page' => $page,
                        ])

                        @if ($recipe->is_choice)
                            <h5 class="mb-0">Choose Reward</h5>
                            <p class="mb-1">
                                This recipe allows you to choose a reward from the following options. Please choose a reward from the dropdown below.
                            </p>
                            <div class="form-group">
                                {!! Form::select('choice_reward_id', $recipe->choiceRewards, null, ['class' => 'form-control choose-reward', 'placeholder' => 'Select Reward']) !!}
                            </div>
                        @endif

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

                    @if ($recipe->is_choice)
                        <h5 class="mb-0">Choose Reward</h5>
                        <p class="mb-1">
                            This recipe allows you to choose a reward from the following options. Please choose a reward from the dropdown below.
                        </p>
                        <div class="form-group">
                            {!! Form::select('choice_reward', $recipe->choiceRewards, null, ['class' => 'form-control choose-reward', 'placeholder' => 'Select Reward']) !!}
                        </div>
                    @endif

                    <div class="text-right">
                        {!! Form::submit('Craft', ['class' => 'btn btn-primary']) !!}
                    </div>
                    {!! Form::close() !!}
                @endif
            @else
                <div class="alert alert-danger mb-0 rounded-0">
                    You do not have all of the required recipe ingredients.
                </div>
            @endif
        </div>
    </div>
@endif

@include('widgets._inventory_select_js')
<script>
    $(document).keydown(function(e) {
        var code = e.keyCode || e.which;
        if (code == 13)
            return false;
    });

    @if ($recipe->is_choice)
        $('.choose-reward').selectize();
    @endif
</script>
