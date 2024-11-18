    @if ($garden->can_use_modifiers)
        <div class="card w-100">
        </div>
    @endif
    <div class="row">
        {{-- @if ($garden->plots->count())
            ACTIONS HERE
            @include('gardens._actions', ['type' => 'seed'])
        @endif --}}
        <div class="{{ $garden->plots->count() ? 'col-9 col-md-9 col-sm-12' : 'col-12' }} justify-content-center">
            <div class="row justify-content-center">
                @if ($garden->plots->count())
                    @foreach ($garden->plots as $plot)
                        <div class="col-md-3 mb-3 text-center">
                            <div class="card text-center justify-content-center" style="max-width: 200px; max-height: 200px; aspect-ratio: 1/1; background-color: rgb(88, 54, 54); background-image: url({{ asset($plot->backgroundImage) }});">
                                <!---- if user owns plot --->
                                @if (Auth::user()->gardenPlots->contains('plot_id', $plot->id) || !\App\Models\Limit\Limit::hasLimits($plot))
                                    @php
                                        $userPlot = Auth::user()
                                            ->gardenPlots()
                                            ->where('plot_id', $plot->id)
                                            ->first();
                                    @endphp
                                    <!--- If plant is dead show dead thing --->
                                    @if ($userPlot?->is_dead)
                                        <div class="btn btn-dark" style="width: 100%; height: 100%;">
                                            {!! Form::open(['url' => 'garden/clear/' . $plot->id]) !!}
                                            <img class="img-fluid my-auto mx-auto pt-2" src="{{ asset('images/garden/dead_seed.png') }}" style="width: 7em; height:7em;">
                                            <div class="text-danger pt-2">Your plant has died.</div>
                                            {!! Form::button('Click to Clear', ['class' => 'btn btn-danger', 'type' => 'submit']) !!}

                                            {!! Form::close() !!}
                                        </div>
                                    @else
                                        @if ($userPlot?->started_at)
                                            {{-- SOMETHING IN PLOT --}}
                                            {{-- @if ($userPlot->actionReady)
                                                @include('garden._action', ['plot' => $plot, 'userPlot' => $userPlot])
                                            @else
                                                @include('garden._wait', ['plot' => $plot, 'userPlot' => $userPlot])
                                            @endif --}}
                                        @else
                                            {{-- NOTHING IN PLOT --}}
                                            <!----If there is NO plant growing --->
                                            @if ($userPlot?->modifiers)
                                                @if ($userPlot?->modifiers['fertiliser'])
                                                    <p class="text-white"><i class="fas fa-star"></i> Fertiliser Quality: {{ $userPlot?->modifiers['fertiliser']['quality'] }}</p>
                                                @endif
                                            @endif
                                            <i class="fas fa-seedling text-white m-1"></i>
                                            <div class="action-button btn btn-success hide mx-2 mt-2" data-plot-id="{{ $userPlot ? $userPlot->id : 'unowned-' . $plot->id }}">
                                                Place
                                            </div>
                                        @endif
                                    @endif
                                @else
                                    <div class="container text-white">
                                        <i class="fas fa-lock text-white m-1"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-12 text-center">
                        <div class="alert alert-info w-100 mb-0">No plots found.</div>
                    </div>
                @endif
            </div>
        </div>
        @if ($garden->plots->count())
            <div class="col-3 col-md-3 col-sm-12">
                @include('gardens._selector', [
                    'plots'               => $garden->plots,
                    'garden'              => $garden,
                    'user_garden_objects' => $user_garden_objects,
                ])
            </div>
        @endif
    </div>
    {{-- <script>


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
