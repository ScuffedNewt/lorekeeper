@if ($drop->parameters)
    @foreach ($drop->parameters as $label => $weight)
        <div class="mb-2">
            <h5>{{ $label }}</h5>
            <div class="form-group">
                @include('widgets._pet_drop_loot_select', [
                    'loots' => $drop->rewards()[strtolower($label)] ?? null,
                    'group' => strtolower(str_replace(' ', '_', $label)),
                    'label' => $label,
                ])
            </div>
        </div>
    @endforeach
@else
    No groups defined for this drop.
@endif
