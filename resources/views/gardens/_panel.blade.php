@if ($garden->plots->count())
    <div class="mb-3">
        @include('gardens.selectors._modifier_selector', [
            'plots' => $garden->plots,
            'garden' => $garden,
            'user_garden_objects' => $user_garden_objects,
        ])
    </div>
@endif
<div class="row no-gutters">
    <div class="{{ !$readOnly ? ($garden->plots->count() ? 'col-9 col-md-9 col-sm-12' : 'col-12') : 'col-12' }} justify-content-center">
        <div class="card">
            <div class="card-body">
                <div class="row justify-content-center">
                    @if ($garden->plots->count())
                        @foreach ($garden->plots as $plot)
                            @php
                                $isPlotUnlocked = $user->gardenPlots->contains('plot_id', $plot->id) || !hasLimits($plot) || hasUnlockedLimits($user, $plot);
                                if ($readOnly && !$isPlotUnlocked) {
                                    continue;
                                }
                            @endphp
                            @include('gardens._plot', [
                                'plot'           => $plot,
                                'readOnly'       => $readOnly,
                                'user'           => $user,
                                'isPlotUnlocked' => $isPlotUnlocked,
                            ])
                        @endforeach
                    @else
                        <div class="col-12 text-center">
                            <div class="alert alert-info w-100 mb-0">No plots found.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if (!$readOnly && $garden->plots->count())
        <div class="col-3 col-md-3 col-sm-12 pl-3">
            @include('gardens.selectors._placeable_selector', [
                'plots' => $garden->plots,
                'garden' => $garden,
                'user_garden_objects' => $user_garden_objects,
            ])
        </div>
    @endif
</div>
