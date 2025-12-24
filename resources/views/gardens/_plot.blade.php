@php
    if (isset($isPlotUnlocked) && $isPlotUnlocked) {
        $userPlot = $user->gardenPlots()->where('plot_id', $plot->id)->first();
    } else {
        $userPlot = null;
    }
@endphp
<div class="col-md-3 mb-3 text-center">
    <div class="plot card text-center justify-content-center text-light" data-plot-id="{{ $userPlot ? $userPlot->id : 'unowned-' . $plot->id }}"
        style="{{ $plot->has_image ? 'background-image: url('.asset($plot->imageUrl).'); background-size: cover; background-position: center; background-repeat: no-repeat;' : '' }}">
        {{-- if user owns plot --}}
        @if ($isPlotUnlocked)
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
                    <div class="plot-info">
                        {{-- NOTHING IN PLOT --}}
                        @if (!$plot->has_image)
                            <i class="fas fa-seedling text-white m-1"></i>
                        @endif
                        @if ($userPlot?->modifiers)
                            @if (isset($userPlot?->modifiers['quality']))
                                <p class="text-white"><i class="fas fa-star"></i> Quality: {{ $userPlot?->modifiers['quality'] }}</p>
                            @endif
                        @endif
                    </div>
                    <div class="action-button hide">
                        <i class="fas fa-map-marker bounce fa-5x"></i>
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