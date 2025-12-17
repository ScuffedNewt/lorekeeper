@if ($object->object->has_image)
    <img class="rounded img-fluid my-auto mx-auto" src="{{ asset($object->stageImage($userPlot->interactedCount) ?? $object->object->imageUrl) }}" data-toggle="tooltip" title="{{ $object->object->name }}"
        style="max-width: 100px; min-width: 50px;"
    />
@else
    <p class="text-light mb-0">
        Currently Growing: 
        <br/>
        {!! $object->object->displayName !!}
    </p>
@endif
@if ($userPlot->canCollect)
    {!! Form::open(['url' => 'gardens/collect/' . $userPlot->id]) !!}
    {!! Form::button('<i class="fas fa-seedling"></i> Harvest', ['class' => 'btn btn-success mb-4', 'type' => 'submit']) !!}
    {!! Form::close() !!}
@else
    @if ($userPlot->actionReady)
        <p class="mb-1">{{ $userPlot->interactedCount }}/{{ $object->data['interactions'] }} Waterings</p>
        @if ($userPlot->interactedCount >= $object->data['interactions'])
            <p class="mb-1">
                Ready to Harvest in:
                <br />
                {!! pretty_date($userPlot->timeUntilReady) !!}
            </p>
        @else
            {!! Form::open(['url' => 'gardens/interact/' . $userPlot->id]) !!}
            {!! Form::button('<i class="fas fa-tint"></i> Water', ['class' => 'btn btn-primary mb-2', 'type' => 'submit']) !!}
            {!! Form::close() !!}
        @endif
    @else
        <p class="mb-1">Can be watered in {!! pretty_date($userPlot->timeUntilReady) !!}</p>
    @endif
@endif
