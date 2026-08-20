@php
    $interactions = (int) ($object->data['interactions'] ?? 0);
    $hours = (float) ($object->data['growth_time'] ?? 0);
@endphp
<div class="card mb-3">
    <div class="card-header h3">Production Cycle Preview</div>
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center">
            <span class="badge badge-primary p-2 m-1">Place {{ $object->object?->name }}</span>
            @for ($step = 1; $step <= $interactions; $step++)
                <span class="mx-1">→</span>
                <span class="badge badge-info p-2 m-1">Wait {{ $hours }}h → {{ config('lorekeeper.garden.garden_object_types.' . $object->type . '.mode_text.tend') }} {{ $step }}</span>
            @endfor
            <span class="mx-1">→</span>
            <span class="badge badge-success p-2 m-1">Wait {{ $hours }}h → {{ config('lorekeeper.garden.garden_object_types.' . $object->type . '.mode_text.collect') }}</span>
        </div>
        @if ($object->type == 'seed')
            <p class="mt-3 mb-0"><strong>Season:</strong> {{ isset($object->data['start_month']) ? Carbon\Carbon::create()->month($object->data['start_month'])->format('F') . '–' . Carbon\Carbon::create()->month($object->data['end_month'] ?? $object->data['start_month'])->format('F') : 'All year' }} · <strong>Perish grace:</strong> {{ $object->data['perish_time'] ?? 24 }}h</p>
        @else
            <p class="mt-3 mb-0"><strong>Collection:</strong> {{ ucfirst($object->data['collection_options']) }} · <strong>Food:</strong> {{ $object->displayFood ?: 'None' }}</p>
        @endif
        @if (in_array($object->type, ['seed', 'animal']) && count($object->tools))
            <p class="mt-3 mb-0"><strong>Required tools:</strong></p>
            <ul class="mb-0">
                @foreach ($object->tools as $tool)
                    <li>Stage {{ $tool->data['stage'] }}: {{ $tool->quantity }}x {!! App\Models\Item\Item::find($tool->rewardable_id)?->displayName ?? 'Missing Item' !!}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
