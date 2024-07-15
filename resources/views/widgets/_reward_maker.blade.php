{!! Form::open(['url' => 'admin/data/reward-maker/edit/' . base64_encode(urlencode(get_class($object))) . '/' . $object->id]) !!}

@php
    // This file represents a common source and definition for assets used in loot_select
    // While it is not per se as tidy as defining these in the controller(s),
    // doing so this way enables better compatibility across disparate extensions
    $items = \App\Models\Item\Item::orderBy('name')->pluck('name', 'id');
    $currencies = \App\Models\Currency\Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id');
    $tables = \App\Models\Loot\LootTable::orderBy('name')->pluck('name', 'id');
    $raffles = \App\Models\Raffle\Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id');
@endphp

<hr style="margin-top: 3em;">

<div class="card mb-3">
    <div class="card-header h2">
        <a href="#" class="btn btn-outline-info float-right" id="addReward">Add Reward</a>
        {{ ucfirst($type) }} Rewards
    </div>
    <div class="card-body" style="clear:both;">
        <p>You can add rewards to this {{ $type }} here.</p>
        <div class="mb-3">
            <table class="table table-sm" id="rewardTable">
                <thead>
                    <tr>
                        <th width="35%">Reward Type</th>
                        <th width="35%">Reward</th>
                        <th width="35%">Quantity</th>
                        <th width="10%"></th>
                    </tr>
                </thead>
                <tbody id="rewardTableBody">
                    @if ($rewards)
                        @foreach ($rewards as $reward)
                            <tr class="reward-row">
                                <td>{!! Form::select('reward_type[]', ['Item' => 'Item', 'Currency' => 'Currency', 'LootTable' => 'Loot Table', 'Raffle' => 'Raffle Ticket'], $reward->reward_type, ['class' => 'form-control reward-type', 'placeholder' => 'Select reward Type']) !!}</td>
                                <td class="reward-row-select">
                                    @if ($reward->reward_type == 'Item')
                                        {!! Form::select('reward_id[]', $items, $reward->reward_id, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item']) !!}
                                    @elseif($reward->reward_type == 'Currency')
                                        {!! Form::select('reward_id[]', $currencies, $reward->reward_id, ['class' => 'form-control currency-select selectize', 'placeholder' => 'Select Currency']) !!}
                                    @elseif($reward->reward_type == 'LootTable')
                                        {!! Form::select('reward_id[]', $tables, $reward->reward_id, ['class' => 'form-control table-select selectize', 'placeholder' => 'Select Loot Table']) !!}
                                    @elseif($reward->reward_type == 'Raffle')
                                        {!! Form::select('reward_id[]', $raffles, $reward->reward_id, ['class' => 'form-control raffle-select selectize', 'placeholder' => 'Select Raffle']) !!}
                                    @endif
                                </td>
                                <td>
                                    {!! Form::number('quantity[]', $reward->quantity, ['class' => 'form-control', 'placeholder' => 'Set Quantity', 'min' => 1]) !!}
                                </td>
                                <td class="text-right"><a href="#" class="btn btn-danger remove-reward-button">Remove</a></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="text-right">
    {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

<hr style="margin-bottom: 3em;">

<div id="rewardRowData" class="hide">
    <table class="table table-sm">
        <tbody id="rewardRow">
            <tr class="reward-row">
                <td>{!! Form::select('reward_type[]', ['Item' => 'Item', 'Currency' => 'Currency', 'LootTable' => 'Loot Table', 'Raffle' => 'Raffle Ticket'], null, ['class' => 'form-control reward-type', 'placeholder' => 'Select Reward Type']) !!}</td>
                <td class="reward-row-select"></td>
                <td>{!! Form::number('quantity[]', null, ['class' => 'form-control', 'placeholder' => 'Set Quantity', 'min' => 1]) !!} </td>
                <td class="text-right"><a href="#" class="btn btn-danger remove-reward-button">Remove</a></td>
            </tr>
        </tbody>
    </table>
    {!! Form::select('reward_id[]', $items, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Item']) !!}
    {!! Form::select('reward_id[]', $currencies, null, ['class' => 'form-control currency-select', 'placeholder' => 'Select Currency']) !!}
    {!! Form::select('reward_id[]', $tables, null, ['class' => 'form-control table-select', 'placeholder' => 'Select Loot Table']) !!}
    {!! Form::select('reward_id[]', $raffles, null, ['class' => 'form-control raffle-select', 'placeholder' => 'Select Raffle']) !!}
</div>


<script>
    $(document).ready(function() {
        var $rewardTable = $('#rewardTableBody');
        var $rewardRow = $('#rewardRow').find('.reward-row');
        var $itemSelect = $('#rewardRowData').find('.item-select');
        var $currencySelect = $('#rewardRowData').find('.currency-select');
        var $tableSelect = $('#rewardRowData').find('.table-select');
        var $raffleSelect = $('#rewardRowData').find('.raffle-select');


        $('#rewardTableBody .selectize').selectize();
        attachRewardTypeListener($('#rewardTableBody .reward-type'));
        attachRemoveListener($('#rewardTableBody .remove-reward-button'));

        $('#addReward').on('click', function(e) {
            e.preventDefault();
            var $clone = $rewardRow.clone();
            $rewardTable.append($clone);
            attachRewardTypeListener($clone.find('.reward-type'));
            attachRemoveListener($clone.find('.remove-reward-button'));
        });

        $('.reward-type').on('change', function(e) {
            var val = $(this).val();
            var $cell = $(this).parent().find('.reward-row-select');

            var $clone = null;
            if (val == 'Item') $clone = $itemSelect.clone();
            else if (val == 'Currency') $clone = $currencySelect.clone();
            else if (val == 'LootTable') $clone = $tableSelect.clone();
            else if (val == 'Raffle') $clone = $raffleSelect.clone();

            $cell.html('');
            $cell.append($clone);
        });

        function attachRewardTypeListener(node) {
            node.on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.reward-row-select');

                var $clone = null;
                if (val == 'Item') $clone = $itemSelect.clone();
                else if (val == 'Currency') $clone = $currencySelect.clone();
                else if (val == 'LootTable') $clone = $tableSelect.clone();
                else if (val == 'Raffle') $clone = $raffleSelect.clone();

                $cell.html('');
                $cell.append($clone);
                $clone.selectize();
            });
        }

        function attachRemoveListener(node) {
            node.on('click', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
            });
        }

    });
</script>
