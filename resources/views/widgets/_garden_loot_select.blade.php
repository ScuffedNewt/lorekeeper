@php
    // This file represents a common source and definition for assets used in loot_select
    // While it is not per se as tidy as defining these in the controller(s),
    // doing so this way enables better compatibility across disparate extensions
    $characterCurrencies = \App\Models\Currency\Currency::where('is_character_owned', 1)
        ->orderBy('sort_character', 'DESC')
        ->pluck('name', 'id');
    $items = \App\Models\Item\Item::orderBy('name')->pluck('name', 'id');
    $currencies = \App\Models\Currency\Currency::where('is_user_owned', 1)
        ->orderBy('name')
        ->pluck('name', 'id');
    if ($showLootTables) {
        $tables = \App\Models\Loot\LootTable::orderBy('name')->pluck('name', 'id');
    }
    if ($showRaffles) {
        $raffles = \App\Models\Raffle\Raffle::where('rolled_at', null)
            ->where('is_active', 1)
            ->orderBy('name')
            ->pluck('name', 'id');
    }
@endphp

<div class="text-right mb-3">
    <a href="#" class="btn btn-outline-info" id="addGarden">Add Reward</a>
</div>
<table class="table table-sm" id="gardenTable">
    <thead>
        <tr>
            <th width="35%">Reward Type</th>
            <th width="35%">Reward</th>
            <th width="20%">Quantity</th>
            <th width="10%"></th>
        </tr>
    </thead>
    <tbody id="gardenTableBody">
        @if ($loots)
            @foreach ($loots as $loot)
                <tr class="garden-row">
                    <td>{!! Form::select('garden_rewardable_type[]', ['Item' => 'Item', 'Currency' => 'Currency'] + ($showLootTables ? ['LootTable' => 'Loot Table'] : []) + ($showRaffles ? ['Raffle' => 'Raffle Ticket'] : []), $loot->garden_rewardable_type, [
                        'class' => 'form-control reward-type',
                        'placeholder' => 'Select Reward Type',
                    ]) !!}</td>
                    <td class="garden-row-select">
                        @if ($loot->garden_rewardable_type == 'Item')
                            {!! Form::select('garden_rewardable_id[]', $items, $loot->garden_rewardable_id, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item']) !!}
                        @elseif($loot->garden_rewardable_type == 'Currency')
                            {!! Form::select('garden_rewardable_id[]', $currencies, $loot->garden_rewardable_id, ['class' => 'form-control currency-select selectize', 'placeholder' => 'Select Currency']) !!}
                        @elseif($showGardenTables && $loot->garden_rewardable_type == 'GardenTable')
                            {!! Form::select('garden_rewardable_id[]', $tables, $loot->garden_rewardable_id, ['class' => 'form-control table-select selectize', 'placeholder' => 'Select Loot Table']) !!}
                        @elseif($showRaffles && $loot->garden_rewardable_type == 'Raffle')
                            {!! Form::select('garden_rewardable_id[]', $raffles, $loot->garden_rewardable_id, ['class' => 'form-control raffle-select selectize', 'placeholder' => 'Select Raffle']) !!}
                        @endif
                    </td>
                    <td>{!! Form::text('garden_quantity[]', $loot->garden_quantity, ['class' => 'form-control']) !!}</td>
                    <td class="text-right"><a href="#" class="btn btn-danger remove-garden-button">Remove</a></td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
