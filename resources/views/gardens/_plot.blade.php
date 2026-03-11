@php
    if (isset($isPlotUnlocked) && $isPlotUnlocked) {
        $userPlot = $user->gardenPlots()->where('plot_id', $plot->id)->first();
    } else {
        $userPlot = null;
    }
@endphp


<div class="plot" data-plot-id="{{ $userPlot ? $userPlot->id : ($isPlotUnlocked ? 'plot-' . $plot->id : 'unowned-' . $plot->id) }}" data-state="empty"
    style="{{ $plot->has_image ? 'background-image: url(' . asset($plot->imageUrl) . '); background-size: cover; background-position: center; background-repeat: no-repeat;' : '' }}">
    <div class="plot-label">{{ $userPlot->plot->name ?? 'Plot' }}</div>
    <div class="plot-center">
        @if ($isPlotUnlocked)
            @if ($userPlot?->is_dead)
            {{-- TODO --}}
                <div class="btn btn-dark" style="width: 100%; height: 100%;">
                    {!! Form::open(['url' => 'garden/clear/' . $plot->id]) !!}
                    <img class="img-fluid my-auto mx-auto pt-2" src="{{ asset('images/garden/dead_seed.png') }}" style="width: 7em; height:7em;">
                    <div class="text-danger pt-2">Your plant has died.</div>
                    {!! Form::button('Click to Clear', ['class' => 'btn btn-danger', 'type' => 'submit']) !!}

                    {!! Form::close() !!}
                </div>
            @else
            {{-- TODO --}}
                @if ($userPlot?->started_at && $userPlot?->object)
                    {{-- SOMETHING IN PLOT --}}
                    @include('gardens.objects._' . $userPlot->object?->type, [
                        'plot' => $plot,
                        'userPlot' => $userPlot,
                        'object' => $userPlot->object,
                    ])
                @else
                    <span class="icon">🌱</span>Empty soil
                @endif
            @endif
        @else
            <div class="unowned">
                <span class="icon">🔒</span>Locked
                @include('widgets._limits', [
                    'object' => $plot,
                    'compact' => true,
                ])
            </div>
        @endif
    </div>
</div>
