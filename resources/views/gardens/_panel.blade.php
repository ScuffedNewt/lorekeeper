<div class="row">
        {{-- @if ($garden->plots->count())
            ACTIONS HERE
            @include('gardens._actions', ['type' => 'seed'])
        @endif --}}
        <div class="{{ !$readOnly ? ($garden->plots->count() ? 'col-9 col-md-9 col-sm-12' : 'col-12') : 'col-12' }} justify-content-center">
            <div class="row justify-content-center">
                @if ($garden->plots->count())
                    @foreach ($garden->plots as $plot)
                        @php
                            $isPlotUnlocked = $user->gardenPlots->contains('plot_id', $plot->id) || !hasLimits($plot) || hasUnlockedLimits($user, $plot);
                            if ($readOnly && !$isPlotUnlocked) {
                                continue;
                            }
                        @endphp
                        <div class="col-md-3 mb-3 text-center">
                            <div class="card text-center justify-content-center text-light"
                                style="max-width: 200px; max-height: 200px; aspect-ratio: 1/1; background-color: rgb(88, 54, 54); {{ $plot->has_image ? 'background-image: url('.asset($plot->imageUrl).'); background-size: cover; background-position: center; background-repeat: no-repeat;' : '' }}">
                                {{-- if user owns plot --}}
                                @if ($isPlotUnlocked)
                                    @php
                                        $userPlot = $user->gardenPlots()->where('plot_id', $plot->id)->first();
                                    @endphp
                                    {{-- If plant is dead show dead thing --}}
                                    @if ($userPlot?->is_dead)
                                        <div class="btn btn-dark" style="width: 100%; height: 100%;">
                                            {!! Form::open(['url' => 'garden/clear/' . $plot->id]) !!}
                                            <img class="img-fluid my-auto mx-auto pt-2" src="{{ asset('images/garden/dead_seed.png') }}" style="width: 7em; height:7em;">
                                            <div class="text-danger pt-2">Your plant has died.</div>
                                            {!! Form::button('Click to Clear', ['class' => 'btn btn-danger', 'type' => 'submit']) !!}

                                            {!! Form::close() !!}
                                        </div>
                                    @else
                                        @if ($userPlot?->started_at && $userPlot?->object)
                                            {{-- SOMETHING IN PLOT --}}
                                            @include('gardens.objects._' . $userPlot->object?->type, [
                                                'plot' => $plot,
                                                'userPlot' => $userPlot,
                                                'object' => $userPlot->object,
                                            ])
                                        @else
                                            {{-- NOTHING IN PLOT --}}
                                            <!----If there is NO plant growing --->
                                            @if ($userPlot?->modifiers)
                                                @if ($userPlot?->modifiers['fertiliser'])
                                                    <p class="text-white"><i class="fas fa-star"></i> Fertiliser Quality: {{ $userPlot?->modifiers['fertiliser']['quality'] }}</p>
                                                @endif
                                            @endif
                                            @if (!$plot->has_image)
                                                <i class="fas fa-seedling text-white m-1"></i>
                                            @endif
                                            <div class="action-button btn btn-success hide mx-2 mt-2" data-plot-id="{{ $userPlot ? $userPlot->id : 'unowned-' . $plot->id }}">
                                                Place
                                            </div>
                                        @endif
                                    @endif
                                @else
                                    {{-- if user does not own plot --}}
                                    <div class="container text-white">
                                        <i class="fas fa-lock text-white m-1"></i>
                                        @include('widgets._limits', [
                                            'object' => $plot,
                                            'compact' => true,
                                        ])
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
        @if (!$readOnly && $garden->plots->count())
            <div class="col-3 col-md-3 col-sm-12">
                @include('gardens._selector', [
                    'plots' => $garden->plots,
                    'garden' => $garden,
                    'user_garden_objects' => $user_garden_objects,
                ])
            </div>
        @endif
    </div>
