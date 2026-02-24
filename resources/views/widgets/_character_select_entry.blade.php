@php
    $characters = \App\Models\Character\Character::visible(Auth::user() ?? null)
        ->myo(0)
        ->orderBy('slug', 'DESC')
        ->get()
        ->pluck('fullName', 'slug')
        ->toArray();
    $items = \App\Models\Item\Item::released()->orderBy('name')->pluck('name', 'id');
    $characterCurrencies = \App\Models\Currency\Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->pluck('name', 'id');
    $skills = \App\Models\Skill\Skill::orderBy('name')->pluck('name', 'id');
    $tables = \App\Models\Loot\LootTable::orderBy('name')->pluck('name', 'id');
    $elements = \App\Models\Element\Element::orderBy('name')->pluck('name', 'id');
    $statuses = \App\Models\Status\StatusEffect::orderBy('name')->pluck('name', 'id');
@endphp

<div class="submission-character mb-3 card">
    <div class="card-body">
        <div class="text-right"><a href="#" class="remove-character text-muted"><i class="fas fa-times"></i></a></div>
        <div class="row">
            <div class="col-md-2 align-items-stretch d-flex">
                <div class="d-flex text-center align-items-center">
                    <div class="character-image-blank hide">Enter character code.</div>
                    <div class="character-image-loaded">
                        @include('home._character', ['character' => $character->character ? $character->character : $character])
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                <div class="form-group">
                    {!! Form::label('slug[]', 'Character Code') !!}
                    {!! Form::select('slug[]', $characters, $character->character ? $character->character->slug : $character->slug, ['class' => 'form-control character-code', 'placeholder' => 'Select Character']) !!}
                </div>
                @if (isset($submission))
                    <div class="form-group col-8">
                        {!! Form::label('character_is_focus[' . ($character->character ? $character->character->id : $character->id) . ']', 'Focus Character?', ['class' => 'mr-2']) !!}
                        <span class="text-muted">
                            Setting the focus character helps staff identify the main character(s) in a submission.
                        </span>
                        {!! Form::select('character_is_focus[' . ($character->character ? $character->character->id : $character->id) . ']', [0 => 'No', 1 => 'Yes'], $character->is_focus, ['class' => 'form-control character-is-focus']) !!}
                    </div>
                @endif
                <div class="character-rewards">
                    <h4>Character Rewards</h4>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                @if ($expanded_rewards)
                                    <th width="35%">Reward Type</th>
                                    <th width="35%">Reward</th>
                                @else
                                    <th width="70%">Reward</th>
                                @endif
                                <th width="30%">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="character-rewards">
                            @foreach ($character->rewards ?? [] as $reward)
                                <tr class="character-reward-row">
                                    @if ($expanded_rewards)
                                        <td>
                                            {!! Form::select(
                                                'character_rewardable_type[' . $character->character_id . '][]',
                                                ['Item' => 'Item', 'Currency' => 'Currency', 'LootTable' => 'Loot Table', 'Element' => 'Element', 'StatusEffect' => 'Status Effect'] +
                                                    (config('lorekeeper.claymores_and_companions.visibility_settings.character_levels') ? ['Experience' => 'Experience'] : []) +
                                                    (config('lorekeeper.claymores_and_companions.visibility_settings.character_stats') ? ['Points' => 'Stat Points'] : []) +
                                                    (config('lorekeeper.claymores_and_companions.visibility_settings.character_skills') ? ['Skill' => 'Skill'] : []) +
                                                    (config('lorekeeper.claymores_and_companions.visibility_settings.character_classes') ? ['Class' => 'Class'] : []),
                                                $reward->rewardable_type,
                                                [
                                                    'class' => 'form-control character-rewardable-type',
                                                    'placeholder' => 'Select Reward Type',
                                                ],
                                            ) !!}
                                        </td>
                                        <td class="lootDivs">
                                            <div class="character-currencies  {{ $reward->rewardable_type == 'Currency' ? 'show' : 'hide' }}">{!! Form::select('character_rewardable_id[' . $character->character_id . '][]', $characterCurrencies, $reward->rewardable_type == 'Currency' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control character-currency-id',
                                                'placeholder' => 'Select Currency',
                                            ]) !!}</div>
                                            <div class="character-items  {{ $reward->rewardable_type == 'Item' ? 'show' : 'hide' }}">{!! Form::select('character_rewardable_id[' . $character->character_id . '][]', $items, $reward->rewardable_type == 'Item' ? $reward->rewardable_id : null, ['class' => 'form-control character-item-id', 'placeholder' => 'Select Item']) !!}</div>
                                            <div class="character-tables {{ $reward->rewardable_type == 'Loot Table' ? 'show' : 'hide' }}">{!! Form::select('character_rewardable_id[' . $character->character_id . '][]', $tables, $reward->rewardable_type == 'Loot Table' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control character-table-id',
                                                'placeholder' => 'Select Loot Table',
                                            ]) !!}</div>
                                            <div class="character-experience {{ $reward->rewardable_type == 'Experience' ? 'show' : 'hide' }}">{!! Form::select('character_rewardable_id[' . $character->character_id . '][]', $experiences, $reward->rewardable_type == 'Experience' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control character-experience-id',
                                                'placeholder' => 'Select Experience',
                                            ]) !!}</div>
                                            <div class="character-elements {{ $reward->rewardable_type == 'Element' ? 'show' : 'hide' }}">{!! Form::select('character_rewardable_id[' . $character->character_id . '][]', $elements, $reward->rewardable_type == 'Element' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control character-element-id',
                                                'placeholder' => 'Select Element',
                                            ]) !!}</div>
                                            <div class="character-statuses  {{ $reward->rewardable_type == 'StatusEffect' ? 'show' : 'hide' }}">{!! Form::select('character_rewardable_id[' . $character->character_id . '][]', $statuses, $reward->rewardable_type == 'StatusEffect' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control character-status-id',
                                                'placeholder' => 'Select Status Effect',
                                            ]) !!}
                                            </div>
                                            <div class="character-skills {{ $reward->rewardable_type == 'Skill' ? 'show' : 'hide' }}">{!! Form::select('character_rewardable_id[' . $character->character_id . '][]', $skills, $reward->rewardable_type == 'Skill' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control character-skill-id',
                                                'placeholder' => 'Select Skill',
                                            ]) !!}</div>
                                            <div class="character-classes {{ $reward->rewardable_type == 'Class' ? 'show' : 'hide' }}">{!! Form::select('character_rewardable_id[' . $character->character_id . '][]', $classes, $reward->rewardable_type == 'Class' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control character-class-id',
                                                'placeholder' => 'Select Class',
                                            ]) !!}</div>
                                            <div class="character-points {{ $reward->rewardable_type == 'Points' ? 'show' : 'hide' }}">{!! Form::select('character_rewardable_id[' . $character->character_id . '][]', $points, $reward->rewardable_type == 'Points' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control character-point-id',
                                                'placeholder' => 'Select Stat Point Type',
                                            ]) !!}</div>
                                        </td>
                                    @else
                                        <td class="lootDivs">
                                            {!! Form::hidden('character_rewardable_type[' . $character->character_id . '][]', 'Currency', ['class' => 'character-rewardable-type']) !!}
                                            {!! Form::select('character_rewardable_id[' . $character->character_id . '][]', $characterCurrencies, $reward->rewardable_type == 'Currency' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control character-currency-id',
                                                'placeholder' => 'Select Currency',
                                            ]) !!}
                                        </td>
                                    @endif
                                    <td class="d-flex align-items-center">
                                        {!! Form::number('character_rewardable_quantity[' . $character->character_id . '][]', $reward->quantity, ['class' => 'form-control mr-2 character-rewardable-quantity']) !!}
                                        <a href="#" class="remove-reward d-block"><i class="fas fa-times text-muted"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-right">
                        <a href="#" class="btn btn-outline-primary btn-sm add-reward">Add Reward</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('[data-toggle="tooltip"]').tooltip();
</script>