<div class="card mb-3">
    <div class="card-header h3">Options</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md form-group">
                {!! Form::label('Number of Feedings?') !!} {!! add_help('The number of times this animal needs to be fed before it can be collected.') !!}
                {!! Form::number('interactions', isset($object) ? $object->data['interactions'] : 1, ['class' => 'form-control', 'min' => 0]) !!}
            </div>
            <div class="col-md form-group">
                {!! Form::label('Collection Options') !!} {!! add_help('Stay keeps the animal in its plot for repeat produce. Remove consumes it. Both lets the user choose each time.') !!}
                {!! Form::select('collection_options', ['both' => 'Stay or Remove (User Choice)', 'stay' => 'Stay Only', 'remove' => 'Remove Only'], isset($object) ? $object->data['collection_options'] : 'stay', ['class' => 'form-control']) !!}
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
                'info' => '<p>These assets will be consumed as food to feed the animal. If no food rewards are added, the animal can be fed without food.</p><p class="mb-0"><strong>Note that ALL assets added will be consumed upon feeding.</strong></p>',
            ])
            {{-- blade-formatter-enable --}}
        @else
            <div class="alert alert-info">
                You can add required food assets to this animal after creating it.
            </div>
        @endif

        <hr />

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header h5">Stay Rewards</div>
                    <div class="card-body">
                        <p>Earned when the animal stays in its plot and begins another production cycle.</p>
                        @include('widgets._loot_select', [
                            'loots' => isset($object) ? $object->rewardsFor('stay') : [],
                            'showLootTables' => true,
                            'showRaffles' => true,
                            'prefix' => 'stay_',
                        ])
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header h5">Removal Rewards</div>
                    <div class="card-body">
                        <p>These rewards will be earned upon removal of the animal from the garden (the user will lose the animal).</p>
                        @include('widgets._loot_select', [
                            'loots' => isset($object) ? $object->rewardsFor('remove') : [],
                            'showLootTables' => true,
                            'showRaffles' => true,
                            'prefix' => 'remove_',
                        ])
                    </div>
                </div>
            </div>
        </div>

        @include('admin.gardens.objects._potential_rewards', ['object' => $object ?? null])

    </div>
</div>

@include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true, 'prefix' => 'stay_'])
@include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true, 'prefix' => 'remove_'])
@include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true, 'prefix' => 'potential_'])

<script>
    $('[data-toggle="tooltip"]').tooltip({
        html: true
    });
</script>
