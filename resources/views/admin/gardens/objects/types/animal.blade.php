<div class="card mb-3">
    <div class="card-header h3">Options</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md form-group">
                {!! Form::label('Number of Feedings?') !!} {!! add_help('The number of times this animal needs to be fed before it can be harvested.') !!}
                {!! Form::number('feedings', isset($object) ? $object->data['feedings'] : 1, ['class' => 'form-control']) !!}
            </div>
            <div class="col-md form-group">
                {!! Form::label('Hunger per Feeding?') !!} {!! add_help('The amount of hunger this animal needs to be satisfied to be considered fed, aka next feeding.') !!}
                {!! Form::number('hunger', isset($object) ? $object->data['hunger'] : 1, ['class' => 'form-control']) !!}
            </div>
            <div class="col-md form-group">
                {!! Form::label('Stays in Garden?') !!} {!! add_help('Does this animal stay in the garden after it is harvested?') !!}
                {!! Form::select('stays', ['1' => 'Yes', '0' => 'No'], isset($object) ? $object->data['stays'] : 1, ['class' => 'form-control']) !!}
            </div>
        </div>

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
