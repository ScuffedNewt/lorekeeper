<img class="img-fluid image-zoom my-auto mx-auto pt-2" src="{{ $userPlot->item->tags->where('tag', $userPlot->plot->plot_type)->first()->stageImage($userPlot->waterings, $userPlot) }}" data-toggle="tooltip"
    title="{{ $userPlot->waterings }} {{ $userPlot->actionTerm }} done!" style="width: 10em; height: 10em;">

<div class="text-white mt-4"> {!! $userPlot->displayHolding !!} </div>
<div class="progress mx-4">
    @php
        if ($userPlot->waterings != 0) {
            $style = ($userPlot->waterings / $userPlot->actionsRequired) * 100;
        } else {
            $style = 0;
        }
    @endphp
    <div class="progress-bar progress-bar-striped progress-bar-animated text-white bg-success" role="progressbar" aria-valuenow="{{ $userPlot->waterings }}" aria-valuemin="0" aria-valuemax="{{ $userPlot->actionsRequired }}"
        style="width: {{ $style }}%">{{ $userPlot->waterings ?? 0 }} / {{ $userPlot->actionsRequired }}
    </div>
</div>

<div id="{{ $userPlot->id }}-water-text" class="text-white">Next {{ $userPlot->actionWord . 'ing' }} at {{ $userPlot->water_at->format('H:i:s') }}</div>
<p class="text-white">Started {!! pretty_date($userPlot->started_at) !!}</p>
<!---- Water times -->
<script>
    var wateringLeft = Date.parse("<?php echo $userPlot->water_at; ?>");
    var waterTextId = "<?php echo $userPlot->id; ?>"
    if (!isSafari) setInterval(waterCount(waterTextId, wateringLeft, "<?php echo $plot->plot_type == 'seed' ? 'seed' : 'animal'; ?>"), 1000);
</script>
<!------------------>
<button type="button" class="skull-button-garden" value="{{ $userPlot->id }}" type="{{ $userPlot->plot->plot_type }}">
    <i class="fas fa-skull-crossbones"></i>
</button>
