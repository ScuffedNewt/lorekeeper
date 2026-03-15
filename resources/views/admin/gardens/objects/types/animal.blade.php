<div class="card mb-3">
    <div class="card-header h3">Options</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md form-group">
                {!! Form::label('Number of Feedings?') !!} {!! add_help('The number of times this animal needs to be fed before it can be collected.') !!}
                {!! Form::number('feedings', isset($object) ? $object->data['feedings'] : 1, ['class' => 'form-control']) !!}
            </div>
            <div class="col-md form-group">
                {!! Form::label('Stays in Garden?') !!} {!! add_help('Does this animal stay in the garden after it is collected? If set to no, it will be debited from the user.') !!}
                {!! Form::select('stays', ['1' => 'Yes', '0' => 'No'], isset($object) ? $object->data['stays'] : 1, ['class' => 'form-control']) !!}
            </div>
        </div>

        @if (isset($object))
            {{-- blade-formatter-disable --}}
            @include('widgets._add_rewards', [
                'prefix' => 'food_',
                'title' => 'Required Food Assets',
                'type'  => 'Food',
                'object' => $object ?? null,
                'useForm' => false,
                'loots' => isset($object) ? $object->food : [],
                'info' => '<p>These assets will be consumed as food to feed the animal. If no food rewards are added, the animal can be fed without food.</p><p><strong>Note that ALL assets added will be consumed upon feeding.</strong></p>',
            ])
            {{-- blade-formatter-enable --}}
        @else
            <div class="alert alert-info">
                You can add required food assets to this animal after creating it.
            </div>
        @endif

        <hr />

        <div class="card mb-3">
            <div class="card-header h5">Definite Rewards</div>
            <div class="card-body">
                <p>These rewards will be earned upon success. If you wish to have potential for, say, better quality produce, use a loot table.</p>
                @include('widgets._loot_select', ['loots' => isset($object) ? $object->rewards : [], 'showLootTables' => true, 'showRaffles' => true])
            </div>
        </div>

        {{-- TODO --}}
        {{-- <div class="card">
            <div class="card-header h5">Potential Rewards</div>
            <div class="card-body">
                <p>This reward has the potential to be obtained if the bonus chance below succeeds.</p>
                <p>To have the chance of "nothing" being rewarded for this seed, use a loot table.</p>
                
                @include('widgets._loot_select', ['loots' => isset($object) ? $object->potentialRewards : [], 'showLootTables' => true, 'showRaffles' => true, 'prefix' => 'potential_'])

                {!! Form::label('Bonus Chance Percentage') !!}
                {!! Form::number('bonus_chance', $object?->data['bonus_chance'] ?? 0, ['class' => 'form-control mb-1', 'min' => 0, 'max' => 100]) !!}
            </div>
        </div> --}}
    </div>
</div>

@include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])
@include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true, 'prefix' => 'potential_'])

<script>
    $('[data-toggle="tooltip"]').tooltip({
        html: true
    });
</script>
