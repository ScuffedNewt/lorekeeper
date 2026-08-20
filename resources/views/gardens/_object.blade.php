@php($readOnly = $readOnly ?? (Auth::id() !== $userPlot->user_id))
@if ($object->object?->has_image)
    <img class="garden-object-image rounded {{ $userPlot->actionReady ? 'plot-ready-image' : 'plot-growing-image' }} img-fluid my-auto mx-auto" src="{{ $object->stageImage($userPlot->interactedCount) ?? $object->object->imageUrl }}" data-toggle="tooltip" title="{{ $object->object->name }}" />
@else
    <p class="text-light mb-0">
        Current:
        <br />
        {!! $object->object->displayName !!}
    </p>
@endif
@if ($userPlot->canCollect)
    <p class="mb-1">
        <i class="fas fa-check-circle" aria-hidden="true"></i>
        Ready to {{ config('lorekeeper.garden.garden_object_types.' . $object->type . '.mode_text.collect') }}!
    </p>
    @if (!$readOnly && $object->type == 'animal' && ($object->data['collection_options'] ?? null) == 'both')
        <div class="animal-collection-options">
            <button type="button" class="btn btn-sm btn-light collect-outcome" data-collection-outcome="stay">Collect &amp; Keep</button>
            <button type="button" class="btn btn-sm btn-danger collect-outcome" data-collection-outcome="remove">Collect &amp; Remove</button>
        </div>
    @endif
@else
    @if ($userPlot->actionReady)
        <div style="top: 10px; right: 5px; z-index: 1; font-size: 1.5em; position: absolute; pointer-events: all;"
            data-toggle="tooltip" title="Ready to {{ config('lorekeeper.garden.garden_object_types.' . $object->type . '.mode_text.tend') }}!">
            <i class="fas fa-heart text-danger"></i>
        </div>
        @if ($object->type == 'animal' && !empty($object->displayFood))
            <div><small>Needs {!! $object->displayFood !!}</small></div>
        @endif
        @if ($requiredTools = $object->displayToolsForStage($userPlot->interactedCount))
            <div><small>Requires tool: {!! $requiredTools !!}</small></div>
        @endif
    @elseif ($userPlot->interactedCount >= $userPlot->actionsRequired)
        <div style="top: 10px; right: 5px; z-index: 1; font-size: 1.5em; position: absolute; pointer-events: all;"
            data-toggle="tooltip" title="Ready to {{ strtolower(config('lorekeeper.garden.garden_object_types.' . $object->type . '.mode_text.collect')) }} at {!! str_replace('"', '', format_date($userPlot->timeUntilReady)) !!}!">
            <i class="fas fa-heart text-info"></i>
        </div>
    @else
        <div style="top: 10px; right: 5px; z-index: 1; font-size: 1.5em; position: absolute; pointer-events: all;"
            data-toggle="tooltip" title="Can be {{ strtolower(config('lorekeeper.garden.garden_object_types.' . $object->type . '.mode_text.tend_past')) }} in<br />{!! str_replace('"', '', format_date($userPlot->timeUntilReady)) !!}">
            <i class="fas fa-heart text-secondary"></i>
        </div>
    @endif
@endif
<div class="progress mt-2" style="height: 12px; pointer-events: all;" data-toggle="tooltip" title="{{ $userPlot->interactedCount }} / {{ $userPlot->actionsRequired }} {{ config('lorekeeper.garden.garden_object_types.' . $object->type . '.mode_text.tend_plural') }}">
    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $userPlot->actionsRequired > 0 ? min(100, $userPlot->interactedCount / $userPlot->actionsRequired * 100) : 100 }}%;" aria-valuenow="{{ $userPlot->interactedCount }}" aria-valuemin="0" aria-valuemax="{{ $userPlot->actionsRequired }}"></div>
</div>
@if ($userPlot->canPerish)
    <small class="d-block mt-1" data-toggle="tooltip" title="This seed will perish if it is not tended to or collected in time.">
        Perishes <span data-countdown="{{ $userPlot->perishesAt->timestamp }}" data-countdown-label="Perished">{!! pretty_date($userPlot->perishesAt) !!}</span>
    </small>
@endif
@if (!$userPlot->actionReady && !$userPlot->canCollect)
    <small class="d-block" data-ready-countdown="{{ $userPlot->timeUntilReady?->timestamp }}">Ready {!! pretty_date($userPlot->timeUntilReady) !!}</small>
@endif
@if (!$readOnly && $object->type == 'animal')
    <button type="button" class="btn btn-sm btn-outline-light mt-1 remove-animal" data-plot-id="{{ $userPlot->id }}">Remove Animal</button>
@endif
