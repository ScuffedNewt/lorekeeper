    <h1>
        <i class="{!! $stat->icon ? $stat->icon . ' mr-2' : '' !!} "></i> {!! $stat->displayName !!} ({{ $stat->abbreviation }})
        <x-admin-edit title="Stat" :object="$stat" />
    </h1>
    <hr class="my-3">
    <div class="row">
        <div class="col-md-6">
            <h4>Base Stat</h4>
            <p>{{ $stat->base }}</p>
        </div>
        <div class="col-md-6">
            <h4>Max Level</h4>
            <p>{{ $stat->max_level ?? 'None' }}</p>
        </div>
    </div>
    <h4>Level Up Information</h4>
    <p>
        @php
            $increment = $stat->increment ?? 1;
            $multiplier = $stat->multiplier ?? 1;
            if (($increment && $stat->base != 0) || ($multiplier && $stat->base != 0)) {
                // Calculate the new stat value
                $newStat = ($stat->base + $increment) * $multiplier;

                // Calculate the percentage increase
                $percentageIncrease = (($newStat - $stat->base) / $stat->base) * 100 . '%';
            } else {
                $newStat = $increment * $multiplier;
                $percentageIncrease = $newStat * 100 . '%';
            }
        @endphp
        This stat increases by <b>{{ $percentageIncrease }}</b> per level up.
        ({{ '(' . $stat->base . ' + ' . $increment . ') * ' . $multiplier . ' = ' . $newStat }})
    </p>
    @if (count($stat->limits))
        <hr class="my-3">
        <h4>Stat Limits</h4>
        <p>
            The stat applies only to the following:
            <br />
            {!! $stat->displayLimits() !!}
        </p>
    @endif
    <hr class="my-3">
    <h2>Equipment</h2>
    <p>The following equipment modify this stat:</p>
    <div class="row">
        @foreach ($stat->equipment as $equipment)
            <div class="col-md-2 text-center">
                @if ($equipment->has_image)
                    <img class="img-fluid rounded" src="{{ $equipment->imageUrl }}" data-toggle="tooltip" title="{{ $equipment->displayWithStats() }}" style="max-width: 75px;" />
                @else
                    {!! $equipment->displayName !!}
                @endif
                <p>
                    {!! $equipment->displayWithStats() !!}
                </p>
            </div>
        @endforeach
    </div>
