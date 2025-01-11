@if ($object->object->has_image)
    <img class="img-fluid my-auto mx-auto" src="{{ asset($object->object->imageUrl) }}" data-toggle="tooltip" title="{{ $object->object->name }}">
@else
    <p class="text-light mb-0">Currently Growing: {!! $object->object->displayName !!}</p>
@endif
@if ($userPlot->canCollect)
    {!! Form::open(['url' => 'gardens/collect/' . $userPlot->id]) !!}
    {!! Form::button('<i class="fas fa-seedling"></i> Harvest', ['class' => 'btn btn-success mb-4', 'type' => 'submit']) !!}
    {!! Form::close() !!}
@else
    @if ($userPlot->actionReady)
        <p class="mb-1">{{ $userPlot->interactedCount }}/{{ $object->data['waterings'] }} Waterings</p>
        {!! Form::open(['url' => 'gardens/interact/' . $userPlot->id]) !!}
        {!! Form::button('<i class="fas fa-tint"></i> Water', ['class' => 'btn btn-primary mb-2', 'type' => 'submit']) !!}
        {!! Form::close() !!}
    @else
        <p class="mb-1">Can be watered in {{ $userPlot->timeUntilReady }}</p>
    @endif
@endif
