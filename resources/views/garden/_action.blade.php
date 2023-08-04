<!--- if we can water, check if the plant / animal is complete or if it's ready to be watered again -->
@if ($userPlot->finishReady)
    <i class="fas fa-seedling text-white mt-1"></i>
    <img class="img-fluid image-zoom my-auto mx-auto pt-2" src="{{ $userPlot->item->tags->where('tag', $userPlot->plot->plot_type)->first()->stageImage($userPlot->waterings, $userPlot) }}" data-toggle="tooltip"
        title="{{ $userPlot->waterings }} {{ $userPlot->actionTerm }} done!" style="width: 10em; height: 10em;">

    <div class="progress mx-4 mb-2 mt-4">
        <div class="progress-bar progress-bar-striped progress-bar-animated text-white bg-success" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="1" style="width: 100%">
            {{ $userPlot->waterings ?? 0 }} / {{ $userPlot->actionsRequired }}
        </div>
    </div>

    <span class="text-light">{!! $userPlot->finishSentence !!}</span>
    <hr class="w-50 mx-auto my-1">
    {!! Form::open(['url' => 'garden/claim/' . $userPlot->id]) !!}
    {!! Form::submit('Claim!', ['class' => 'btn btn-sm btn-primary mb-1']) !!}
    {!! Form::close() !!}
@else
    {!! Form::open(['url' => 'garden/action/' . $userPlot->id]) !!}
    <img class="img-fluid image-zoom my-auto mx-auto pt-2" src="{{ $userPlot->item->tags->where('tag', $userPlot->plot->plot_type)->first()->stageImage($userPlot->waterings) }}" data-toggle="tooltip"
        title="{{ $userPlot->waterings }} {{ $userPlot->actionTerm }} done!" style="width: 9em; height: 9em;">
    @php $text = $userPlot->actionSentence.'<p class="text-white">Started '. pretty_date($userPlot->started_at).'</p>'; @endphp
    {!! Form::button($text, ['class' => 'btn btn-primary btn-block pt-3', 'type' => 'submit']) !!}
    {!! Form::close() !!}
@endif
