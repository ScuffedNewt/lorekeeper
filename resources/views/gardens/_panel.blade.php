    @if ($garden->can_use_modifiers)
        <div class="card w-100">
        </div>
    @endif
    <div class="row">
        {{-- @if ($garden->plots->count())
            ACTIONS HERE
            @include('gardens._actions', ['type' => 'seed'])
        @endif --}}
        <div class="col-9 col-md-9 col-sm-12 justify-content-center">
            <div class="row">
                @if ($garden->plots->count())
                    @foreach ($garden->plots as $plot)
                        <div class="col-md-3 mb-3 text-center">
                            <div class="card text-center justify-content-center"
                                style="max-width: 200px; max-height: 200px; aspect-ratio: 1/1; background-color: rgb(88, 54, 54); background-image: url({{ asset($plot->backgroundImage) }});">
                                <!---- if user owns plot --->
                                @if (Auth::user()->gardenPlots->contains('plot_id', $plot->id))
                                    @php
                                        $userPlot = Auth::user()
                                            ->gardenPlots()
                                            ->where('plot_id', $plot->id)
                                            ->first();
                                    @endphp
                                    <!--- If plant is dead show dead thing --->
                                    @if ($userPlot->is_dead)
                                        <div class="btn btn-dark" style="width: 100%; height: 100%;">
                                            {!! Form::open(['url' => 'garden/clear/' . $plot->id]) !!}
                                            <img class="img-fluid my-auto mx-auto pt-2" src="{{ asset('images/garden/dead_seed.png') }}" style="width: 7em; height:7em;">
                                            <div class="text-danger pt-2">Your plant has died.</div>
                                            {!! Form::button('Click to Clear', ['class' => 'btn btn-danger', 'type' => 'submit']) !!}

                                            {!! Form::close() !!}
                                        </div>
                                    @else
                                        @if ($userPlot->started_at)
                                            <!----If there is a plant growing / animal placed, and it is not time to water / feed --->
                                            @if ($userPlot->actionReady)
                                                @include('garden._action', ['plot' => $plot, 'userPlot' => $userPlot])
                                            @else
                                                @include('garden._wait', ['plot' => $plot, 'userPlot' => $userPlot])
                                            @endif
                                        @else
                                            <!----If there is NO plant growing --->
                                            <i class="fas fa-seedling text-white m-1"></i>
                                            @if ($plot->plot_type == 'seed')
                                                @if ($userPlot->modifiers != null)
                                                    @php $mods = json_decode($userPlot->modifiers, true); @endphp
                                                    @if ($mods['fertiliser'])
                                                        <p class="text-white"><i class="fas fa-star"></i> Fertiliser Quality: {{ $mods['fertiliser']['quality'] }}</p>
                                                    @endif
                                                @endif
                                                <button type="button" class="seed-button-plant btn btn-success hide" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="plantSeed({{ $userPlot->id }}, 'seed')">
                                                    Plant Seed!
                                                </button>
                                                <button type="button" class="mod-button-plant btn btn-warning text-white hide" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="addModifier({{ $userPlot->id }})">
                                                    Apply Modifier!
                                                </button>
                                            @else
                                                <button type="button" class="{{ $plot->plot_type }}-button-plant btn btn-success hide" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button"
                                                    onclick="placeAnimal({{ $userPlot->id }}, '{{ $userPlot->plot->plot_type }}')">
                                                    Place Animal!
                                                </button>
                                            @endif
                                        @endif
                                    @endif
                                @else
                                    <div class="container text-white">
                                        @if (\App\Models\Limit\Limit::hasLimits($plot))

                                        @else
                                            <i class="fas fa-seedling text-white m-1"></i>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info mb-0">No plots found.</div>
                @endif
            </div>
        </div>
        <div class="col-3 col-md-3 col-sm-12">
            @include('gardens._selector', [
                'user_garden_objects' => $user_garden_objects,
                'plots' => $garden->plots,
            ])
        </div>
    </div>
    {{-- <script>
        $("#seedSelector input:radio").change(function() {
            selectedSeed = $(this).val();
            if (selectedSeed == null || selectedSeed == 0) {
                $(".seed-button-plant").each(function() {
                    $(this).addClass('hide');
                })
            } else {
                $(".seed-button-plant").each(function() {
                    $(this).removeClass('hide');
                })
                $(".mod-button-plant").each(function() {
                    $(this).addClass('hide');
                })
            }
        });

        $("#barnSelector input:radio").change(function() {
            selectedBarn = $(this).val();
            if (selectedBarn == null || selectedBarn == 0) {
                $(".barn-button-plant").each(function() {
                    $(this).addClass('hide');
                });
            } else {
                $(".barn-button-plant").each(function() {
                    $(this).removeClass('hide');
                });
            }
        });

        $("#coopSelector input:radio").change(function() {
            selectedCoop = $(this).val();
            if (selectedCoop == null || selectedCoop == 0) {
                $(".coop-button-plant").each(function() {
                    $(this).addClass('hide');
                });
            } else {
                $(".coop-button-plant").each(function() {
                    $(this).removeClass('hide');
                });
            }
        });


        $("#modifierSelector input:radio").change(function() {
            if ('<?php echo $plots->first()->plot_type; ?>' == 'seed') {
                selectedModifier = $(this).val();
                if (selectedModifier == null || selectedModifier == 0) {
                    $(".mod-button-plant").each(function() {
                        $(this).addClass('hide');
                    })
                } else {
                    $(".seed-button-plant").each(function() {
                        $(this).addClass('hide');
                    })
                    $(".mod-button-plant").each(function() {
                        $(this).removeClass('hide');
                    })
                }
            }
        });

        $('.skull-button-garden').on('click', function(e) {
            e.preventDefault();
            var type = $(this).attr('type');
            loadModal("{{ url('garden/skull') }}" + '/' + $(this).val(), (type == 'seed' ? 'Destroy Crop?' : 'Remove Animal?'));
        });

        function plantSeed(plot_id) {
            // disable plant class
            $(".seed-button-plant").each(function() {
                $(this).addClass('hide');
            });
            if (selectedSeed == null) {
                alert("Please select a seed to add!");
            } else {
                $.ajax({
                    url: "{{ url('garden/plant') }}",
                    method: 'post',
                    data: {
                        _token: '{{ csrf_token() }}',
                        plot_id: plot_id,
                        item_id: selectedSeed,
                        type: 'seed'
                    },
                    dataType: 'json',
                    success: function(data) {
                        refreshThePage();
                    },
                    error: function(error) {
                        console.log(error);
                        // refreshThePage();
                    }
                });
            }
        }

        function placeAnimal(plot_id, plot_type) {
            if (plot_type == 'coop' && selectedCoop == null) {
                alert("Please select an animal!");
            }
            if (plot_type == 'barn' && selectedBarn == null) {
                alert("Please select an animal!");
            }
            $.ajax({
                url: "{{ url('garden/plant') }}",
                method: 'post',
                data: {
                    _token: '{{ csrf_token() }}',
                    plot_id: plot_id,
                    item_id: (plot_type == 'coop' ? selectedCoop : selectedBarn),
                    type: plot_type
                },
                dataType: 'json',
                success: function(data) {
                    refreshThePage();
                },
                error: function(error) {
                    refreshThePage();
                }
            });
        }

        function addModifier(plot_id) {
            if (selectedModifier == null) {
                alert("Please select a modifier!");
            } else {
                $.ajax({
                    url: "{{ url('garden/modify') }}",
                    method: 'post',
                    data: {
                        _token: '{{ csrf_token() }}',
                        plot_id: plot_id,
                        mod_id: selectedModifier
                    },
                    dataType: 'json',
                    success: function(data) {
                        refreshThePage();
                    },
                    error: function(error) {
                        refreshThePage();
                    }
                });
            }
        }

        function refreshThePage() {
            location.reload();
        }
    </script> --}}
