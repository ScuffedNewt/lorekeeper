<div class="row world-entry">
    @if ($imageUrl)
        <div class="col-md-3 world-entry-image">
            <a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}">
                <img src="{{ $imageUrl }}" />
            </a>
        </div>
    @endif
    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
        <x-admin-edit title="Recipe" :object="$recipe" />
        <h3 class="mb-0">
            @if (!$recipe->is_visible)
                <i class="fas fa-eye-slash mr-1"></i>
            @endif
            {!! $name !!}
            @if (isset($idUrl) && $idUrl)
                <a href="{{ $idUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a>
            @endif
        </h3>
        @if ($recipe->category)
            <div class="mb-1">
                <span class="font-weight-bold">Category:</span>
                @if (!$recipe->category->is_visible)
                    <i class="fas fa-eye-slash mx-1 text-danger"></i>
                @endif
                {!! $recipe->category->displayName !!}
            </div>
        @endif
        <div> 
            @if ($recipe->needs_unlocking || hasLimits($recipe))
                @if (Auth::check())
                    @if ($recipe->hasUserUnlocked(Auth::user()))
                        <div class="alert alert-success row no-gutters align-items-center my-2" style="font-size: 1.25em;">
                            <div class="col-auto pr-2">
                                <i class="fas fa-lock-open" aria-hidden="true"></i>
                            </div>
                            <div class="col text-center text-md-left">
                                You <b>have unlocked</b> and own this recipe!
                            </div>
                        </div>
                    @else
                        <div class="alert alert-danger row no-gutters align-items-center my-2" style="font-size: 1.25em;">
                            <div class="col-auto pr-2">
                                <i class="fas fa-lock" aria-hidden="true"></i>
                            </div>
                            <div class="col text-center text-md-left">
                                You have <b>not yet unlocked</b> and do not have this recipe.
                            </div>
                        </div>
                    @endif
                @else
                    <div class="alert alert-danger row no-gutters align-items-center my-2" style="font-size: 1.25em;">
                        <div class="col-auto pr-2">
                            <i class="fas fa-lock" aria-hidden="true"></i>
                        </div>
                        <div class="col text-center text-md-left">
                            You <b>must be logged in</b> to unlock this recipe.
                        </div>
                    </div>
                @endif
            @else
                <div class="alert alert-success row no-gutters align-items-center my-2" style="font-size: 1.25em;">
                    <div class="col-auto pr-2">
                        <i class="fas fa-lock-open" aria-hidden="true"></i>
                    </div>
                    <div class="col text-center text-md-left">
                        This recipe is <b>automatically unlocked.</b>
                    </div>
                </div>
            @endif
        </div>
        <hr class="mb-0">
        <div class="row no-gutters">
            @if (hasLimits($recipe))
                <div class="col-12 mb-2">
                    @include('widgets._limits', [
                        'object' => $recipe,
                    ])
                    <hr />
                </div>
            @endif
            <div class="col-md-6 pr-md-1">
                <h5 class="mb-0">Ingredients</h5>
                @for ($i = 0; $i < count($recipe->ingredients) && $i < 3; ++$i)
                    <?php $ingredient = $recipe->ingredients[$i]; ?>
                    <div class="alert alert-secondary mb-1">
                        @include('home.crafting._recipe_ingredient_entry', ['ingredient' => $ingredient])
                    </div>
                @endfor
                @if (count($recipe->ingredients) > 3)
                    <i class="fas fa-ellipsis-h mb-3"></i>
                @endif
            </div>
            <div class="col-md-6 pl-md-1">
                <h5 class="mb-0">Rewards</h5>
                <?php $counter = 0; ?>
                @foreach (parseAssetData($recipe->output) as $type)
                    @foreach ($type as $item)
                        @if ($counter > 3)
                            @break
                        @endif
                        <?php ++$counter; ?>
                        <div class="alert alert-secondary mb-1">
                            @include('home.crafting._recipe_reward_entry', [
                                'reward' => $item,
                            ])
                        </div>
                    @endforeach
                @endforeach
                @if ($counter > 3)
                    <i class="fas fa-ellipsis-h mb-3"></i>
                @endif
            </div>
        </div>
    </div>
</div>
