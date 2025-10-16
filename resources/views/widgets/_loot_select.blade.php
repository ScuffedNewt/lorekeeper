@php
    // This file represents a common source and definition for assets used in loot_select
    // While it is not per se as tidy as defining these in the controller(s),
    // doing so this way enables better compatibility across disparate extensions
    $characterCurrencies = \App\Models\Currency\Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->pluck('name', 'id');
    $items = \App\Models\Item\Item::orderBy('name')->pluck('name', 'id');
    $currencies = \App\Models\Currency\Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id');
    if ($showLootTables) {
        $tables = \App\Models\Loot\LootTable::orderBy('name')->pluck('name', 'id');
    }
    if ($showRaffles) {
        $raffles = \App\Models\Raffle\Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id');
    }
    if (!isset($prefix)) {
        $prefix = '';
    }
@endphp

<div class="text-right mb-3">
    <a href="#" class="btn btn-outline-info" id="{{ $prefix }}addLoot">Add Reward</a>
</div>
<table class="table table-sm" id="{{ $prefix }}lootTable">
    <thead>
        <tr>
            <th width="{{ isset($extra_fields) ? '25%' : '35%' }}">Reward Type</th>
            <th width="{{ isset($extra_fields) ? '25%' : '35%' }}">Reward</th>
            <th width="{{ isset($extra_fields) ? '15%' : '20%' }}">Quantity</th>
            @if (isset($extra_fields))
                @foreach ($extra_fields as $field => $data)
                    <th>{{ $data['label'] }}</th>
                @endforeach
            @endif
            <th width="10%"></th>
        </tr>
    </thead>
    <tbody id="{{ $prefix }}lootTableBody">
        @if ($loots)
            @foreach ($loots as $loot)
                <tr class="{{ $prefix }}loot-row">
                    <td>{!! Form::select($prefix . 'rewardable_type[]', ['Item' => 'Item', 'Currency' => 'Currency'] + ($showLootTables ? ['LootTable' => 'Loot Table'] : []) + ($showRaffles ? ['Raffle' => 'Raffle Ticket'] : []), $loot->rewardable_type, [
                        'class' => 'form-control ' . $prefix . 'reward-type',
                        'placeholder' => 'Select Reward Type',
                    ]) !!}</td>
                    <td class="{{ $prefix }}loot-row-select">
                        @if ($loot->rewardable_type == 'Item')
                            {!! Form::select($prefix . 'rewardable_id[]', $items, $loot->rewardable_id, ['class' => 'form-control ' . $prefix . 'item-select selectize', 'placeholder' => 'Select Item']) !!}
                        @elseif($loot->rewardable_type == 'Currency')
                            {!! Form::select($prefix . 'rewardable_id[]', $currencies, $loot->rewardable_id, ['class' => 'form-control ' . $prefix . 'currency-select selectize', 'placeholder' => 'Select Currency']) !!}
                        @elseif($showLootTables && $loot->rewardable_type == 'LootTable')
                            {!! Form::select($prefix . 'rewardable_id[]', $tables, $loot->rewardable_id, ['class' => 'form-control ' . $prefix . 'table-select selectize', 'placeholder' => 'Select Loot Table']) !!}
                        @elseif($showRaffles && $loot->rewardable_type == 'Raffle')
                            {!! Form::select($prefix . 'rewardable_id[]', $raffles, $loot->rewardable_id, ['class' => 'form-control ' . $prefix . 'raffle-select selectize', 'placeholder' => 'Select Raffle']) !!}
                        @endif
                    </td>
                    <td>{!! Form::text($prefix . 'quantity[]', $loot->quantity, ['class' => 'form-control']) !!}</td>
                    @if (isset($extra_fields))
                        @foreach ($extra_fields as $field => $data)
                            <td>
                                @php
                                    $field_name = $prefix . $field . '[]';
                                    $value = $loot->$field ?? ($data['default'] ?? null);
                                    $attributes = $data['attributes'] ?? [];
                                @endphp
                                {!! Form::{$data['type']}($field_name, $value, array_merge(['class' => 'form-control', 'placeholder' => $data['label']], $attributes)) !!}
                            </td>
                        @endforeach
                    @endif
                    <td class="text-right"><a href="#" class="btn btn-danger {{ $prefix }}remove-loot-button">Remove</a></td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
