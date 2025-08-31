@extends('home.layout')

@section('home-title')
    Crafting
@endsection

@section('home-content')
    {!! breadcrumbs(['Crafting' => 'crafting']) !!}

    <h1>
        My Recipe Book
    </h1>
    <p>This is a list of recipes that you have unlocked, as well as automatically unlocked recipes.</p>

    <div class="row no-gutters">
        <div class="col-md-6 pr-md-1 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h3>Free Recipes</h3>
                </div>
                <div class="card-body">
                    @if ($default->count())
                        <div class="row no-gutters">
                            @foreach ($default as $recipe)
                                @include('home.crafting._smaller_recipe_card', ['recipe' => $recipe])
                            @endforeach
                        </div>
                    @else
                        There are no free recipes.
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6 pl-md-1 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h3>Your Unlocked Recipes</h3>
                </div>
                <div class="card-body">
                    @if (Auth::user()->recipes->count())
                        <div class="row no-gutters">
                            @foreach (Auth::user()->recipes as $recipe)
                                @include('home.crafting._smaller_recipe_card', ['recipe' => $recipe])
                            @endforeach
                        </div>
                    @else
                        You haven't unlocked any recipes!
                    @endif
                    <div class="text-right mt-2">
                        <a href="{{ url(Auth::user()->url . '/recipe-logs') }}">View logs...</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header h3">
            Crafting Recipe Slots
        </div>
        <div class="card-body">
            <p class="mb-1">
                Recipe slots are used for recipes that require time to craft. Each slot can have a cost associated with it, which is paid to unlock the slot OR whenever a recipe is crafted.
            </p>
            <p>Each slot can only be used for one recipe at a time.</p>
            <hr />
            <div class="row ml-2">
                @foreach ($slots as $slot)
                    <div class="col-sm-6 col-md-4 col-lg-3 h-100 p-1">
                        <div class="card bg-secondary text-center d-flex justify-content-center align-items-center mx-auto" style="height: 200px; max-width: 200px;">
                            <div class="h5 text-white mb-0 mt-2">
                                <i class="fas fa-tools"></i>
                                {{ $slot->displayName }}
                            </div>
                            @if ($slot->hasUserUnlocked(Auth::user()))
                                @if ($slot->userSlot(Auth::user())->isCrafting)
                                    @if ($slot->userSlot(Auth::user())->recipe->has_image)
                                        <img src="{{ $slot->userSlot(Auth::user())->recipe->imageUrl }}" class="my-auto" style="max-width: 75px; max-height:75px;">
                                    @endif
                                    @php
                                        $now = Carbon\Carbon::now();
                                        $diff = $now->diffInMinutes($slot->userSlot(Auth::user())->end_at, false);
                                    @endphp
                                    @if ($slot->userSlot(Auth::user())->end_at >= $now)
                                        <div class="text-white">
                                            @if ($diff >= 0 && $diff < 1)
                                                1> minute till you finish crafting!
                                            @else
                                                {{ $diff }} minutes till you finish crafting!
                                            @endif
                                        </div>
                                        <p>Started {!! pretty_date($slot->userSlot(Auth::user())->started_at) !!}
                                    @else
                                        {!! Form::open(['url' => 'crafting/claim/' . $slot->userSlot(Auth::user())->id]) !!}
                                        {!! Form::submit('Claim!', ['class' => 'btn btn-sm btn-primary mb-2']) !!}
                                        {!! Form::close() !!}
                                    @endif
                                @else
                                    <div class="text-white">
                                        It's idle...
                                    </div>
                                @endif
                            @else
                                @if (hasLimits($slot))
                                    <div class="mb-0" style="width: 75%;">
                                        @include('widgets._limits', [
                                            'object' => $slot,
                                            'compact' => true,
                                            'hideUnlock' => true,
                                        ])
                                    </div>
                                    <div class="text-white mt-0" style="font-size: 0.7rem;">
                                        @if (getLimits($slot)->first()->is_unlocked)
                                            You will only have to have the limits when unlocking this slot.
                                        @else
                                            You do not need to meet the limits to unlock this slot, but will have to meet them to use the slot.
                                        @endif
                                    </div>
                                @endif
                                {!! Form::open(['url' => 'crafting/slot/unlock/' . $slot->id]) !!}
                                    {!! Form::submit('Unlock!', ['class' => 'btn btn-sm btn-primary']) !!}
                                {!! Form::close() !!}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection


@section('scripts')
    <script>
        $(document).ready(function() {
            $('.btn-craft').on('click', function(e) {
                e.preventDefault();
                var $parent = $(this).parent().parent().parent();
                loadModal("{{ url('crafting/craft') }}/" + $parent.data('id'), $parent.data('name'));
            });
        });
    </script>
@endsection
