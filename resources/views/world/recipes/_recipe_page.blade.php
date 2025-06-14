@extends('world.layout')

@section('title')
    {{ $recipe->name }}
@endsection

@section('meta-img')
    {{ $recipe->imageUrl ? $recipe->imageUrl : null }}
@endsection

@section('meta-desc')
    {!! substr(str_replace('"', '&#39;', $recipe->description), 0, 69) !!}
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'recipes' => 'world/recipes', $recipe->name => $recipe->idUrl]) !!}

    <div class="row">
        <div class="col-lg-6 col-lg-10 mx-auto">
            <div class="card mb-3">
                <div class="card-body">
                    @if ($recipe->imageUrl)
                        <div class="world-entry-image text-center mb-2">
                            <a href="{{ $recipe->imageUrl }}" data-lightbox="entry" data-title="{{ $recipe->name }}">
                                <img src="{{ $recipe->imageUrl }}" class="world-entry-image" style="max-height:300px;" />
                            </a>
                        </div>
                    @endif
                    <div>
                        <x-admin-edit title="Recipe" :object="$recipe" />
                        <h1 class="mb-0">
                            @if (!$recipe->is_visible)
                                <i class="fas fa-eye-slash mr-1"></i>
                            @endif
                            {!! $recipe->name !!}
                        </h1>
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
                            @if ($recipe->needs_unlocking)
                                @if (Auth::check() && Auth::user()->hasRecipe($recipe->id))
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
                        @if (isset($recipe->description) && $recipe->description)
                            <div class="card card-body world-entry-text">
                                {!! $recipe->description !!}
                            </div>
                        @endif
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
                                @foreach ($recipe->ingredients as $ingredient)
                                    <div class="alert alert-secondary mb-1">
                                        @include('home.crafting._recipe_ingredient_entry', ['ingredient' => $ingredient])
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-md-6 pl-md-1">
                                <h5 class="mb-0">Rewards</h5>
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
                        @if (!$recipe->needs_unlocking || (Auth::check() && Auth::user()->hasRecipe($recipe->id)))
                            <div class="text-center mt-2">
                                <a href="{{ url('crafting') }}" class="btn btn-primary h5 text-white">
                                    Craft this from your Recipe Book!
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
