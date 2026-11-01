@php
    if (isset($isPlotUnlocked) && $isPlotUnlocked) {
        $userPlot = $user->gardenPlots()->where('plot_id', $plot->id)->first();
        $guestWaterLimit = (int) config('lorekeeper.garden.user_guest_water_limit');
        $guestWithinWaterLimit = !$guestWaterLimit || (Auth::user()?->activity?->garden_guest_waters_today ?? 0) < $guestWaterLimit;
        $canGuestTend = $readOnly && Auth::check() && Auth::id() != $user->id && config('lorekeeper.garden.allow_users_to_water_gardens') && $guestWithinWaterLimit && $userPlot?->object?->type == 'seed' && $userPlot?->actionReady;
        $canInteract = (!$readOnly && $userPlot?->actionReady && !$userPlot?->canCollect) || $canGuestTend;
        $canCollect = !$readOnly && $userPlot?->canCollect;
        $empty = !($userPlot?->started_at && $userPlot?->object);
    } else {
        $userPlot = null;
        $canInteract = false;
        $canCollect = false;
        $empty = true;
    }

    $plotBackgroundImage = null;
    if ($plot->has_image) {
        $plotBackgroundImage = asset($plot->imageUrl);
    } elseif ($garden->has_plot_image) {
        $plotBackgroundImage = asset($garden->plotImageUrl);
    }
@endphp

<div class="plot {{ $empty ? 'empty' : '' }} {{ $canCollect ? 'collect' : ($canInteract ? 'tend' : '') }} {{ $plotBackgroundImage ? 'has-plot-image' : '' }}"
    data-plot-id="{{ $userPlot ? $userPlot->id : ($isPlotUnlocked ? 'plot-' . $plot->id : 'unowned-' . $plot->id) }}" data-state="{{ $empty ? 'empty' : 'filled' }}" data-read-only="{{ $readOnly ? 1 : 0 }}"
    style="{{ $plotBackgroundImage ? 'background-image: url(' . $plotBackgroundImage . ');' : '' }}">
    <div class="plot-label">
        {{ $userPlot?->plot?->name }}
    </div>
    <div class="plot-center" id="plot-center-{{ $userPlot ? $userPlot->id : ($isPlotUnlocked ? 'plot-' . $plot->id : 'unowned-' . $plot->id) }}">
        @if ($isPlotUnlocked)
            @if ($userPlot?->isPerished)
                @include('gardens._perished', ['userPlot' => $userPlot])
            @else
                @if (!$empty)
                    @include('gardens._object', [
                        'plot' => $plot,
                        'userPlot' => $userPlot,
                        'object' => $userPlot->object,
                        'readOnly' => $readOnly,
                    ])
                @else
                    @if (!$plotBackgroundImage)
                        <span class="icon"><i class="fas fa-plus" aria-hidden="true"></i></span>Empty plot
                    @endif
                @endif
            @endif
        @else
            <div class="unowned">
                <span class="icon"><i class="fas fa-lock" aria-hidden="true"></i></span>Locked
                @include('widgets._limits', [
                    'object' => $plot,
                    'compact' => true,
                ])
            </div>
        @endif
    </div>
</div>
