<div class="card mb-3">
    <div class="card-header h5">Required Tools</div>
    <div class="card-body">
        <p>
            Required tools are used automatically when the object is tended. Stage 0 is the first watering or feeding,
            stage 1 is the next action after one completed interaction, and so on.
        </p>
        <div class="alert alert-info">
            Only select item assets that are also configured as Tool garden objects. Each use follows the item's
            <strong>Degradable</strong> tag and its Garden usage rate; an item without that tag is consumed.
        </div>
        @include('widgets._loot_select', [
            'loots' => isset($object) ? $object->tools : [],
            'prefix' => 'tool_',
            'type' => 'Tool',
            'extra_fields' => [
                'stage' => [
                    'type' => 'number',
                    'label' => 'Required at Stage',
                    'attributes' => ['min' => 0, 'step' => 1],
                    'default' => 0,
                ],
            ],
        ])
    </div>
</div>
