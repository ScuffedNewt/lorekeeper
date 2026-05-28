<div class="card mb-3">
    <div class="card-header h5">Potential Rewards</div>
    <div class="card-body">
        <p>Each reward is rolled independently when the object is collected. A failed roll awards nothing for that row.</p>
        @include('widgets._loot_select', [
            'loots' => isset($object) ? $object->potentialRewards : [],
            'showLootTables' => true,
            'showRaffles' => true,
            'prefix' => 'potential_',
            'extra_fields' => [
                'chance' => [
                    'type' => 'number',
                    'label' => 'Chance (%)',
                    'attributes' => ['step' => '0.01', 'min' => 0, 'max' => 100],
                    'default' => 100,
                ],
            ],
        ])
    </div>
</div>
