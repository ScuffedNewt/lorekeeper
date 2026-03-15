@if ($object->object?->has_image)
    <img class="rounded img-fluid my-auto mx-auto" src="{{ asset($object->stageImage($userPlot->interactedCount) ?? $object->object->imageUrl) }}" data-toggle="tooltip" title="{{ $object->object->name }}" style="max-width: 100px;" />
@else
    <p class="text-light mb-0">
        Currently Growing:
        <br />
        {!! $object->object->displayName !!}
    </p>
@endif
@if ($userPlot->canCollect)
    <p class="mb-1">
        🎑 Ready to {{ config('lorekeeper.garden.garden_object_types.' . $object->type . '.mode_text.collect') }}!
    </p>
@else
    <p class="mb-1">
        {{ $userPlot->interactedCount }}/{{ $userPlot->actionsRequired }} {{ config('lorekeeper.garden.garden_object_types.' . $object->type . '.mode_text.tend_plural') }}
    </p>

    @if ($userPlot->actionReady)
        <p class="mb-1">
            ❤️ Ready to {{ config('lorekeeper.garden.garden_object_types.' . $object->type . '.mode_text.tend') }}!
        </p>
        @if ($object->type == 'animal' && isset($object->displayFood))
            <small>Needs {!! $object->displayFood !!}</small>
        @endif
    @elseif ($userPlot->interactedCount >= $userPlot->actionsRequired)
        <div class="alert alert-info mb-0 p-1 px-2">
            Ready to {{ config('lorekeeper.garden.garden_object_types.' . $object->type . '.mode_text.collect') }} in<br />{!! pretty_date($userPlot->timeUntilReady) !!}
        </div>
    @else
        <div class="alert alert-info mb-0 p-1 px-2">
            Can be {{ config('lorekeeper.garden.garden_object_types.' . $object->type . '.mode_text.tend_past') }} in<br />{!! pretty_date($userPlot->timeUntilReady) !!}
        </div>
    @endif
@endif
