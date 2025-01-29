<?php

return [

    'limit_types' => [
        'prompt' => [
            'name'        => 'Prompts',
            'description' => 'Prompt limits require a user to have submitted to the specified prompt a certain number of times.',
        ],
        'item' => [
            'name'        => 'Items',
            'description' => 'Item limits require a user to have a certain number of items in their inventory.',
        ],
        'currency' => [
            'name'        => 'Currency',
            'description' => 'Currency limits require a user to have a certain amount of currency.',
        ],
        'dynamic' => [
            'name'        => 'Dynamic',
            'description' => 'Dynamic limits require a user to meet a certain condition. The condition is evaluated at runtime.',
        ],
        'stat' => [
            'name'        => 'Stats',
            'description' => 'Stat limits require a character to have a certain amount of a specific stat.',
        ],
        'level' => [
            'name'        => 'Levels',
            'description' => 'Level limits require a character OR user to have a certain level.',
        ],
        'class' => [
            'name'        => 'Classes',
            'description' => 'Class limits require a character to have a certain class.',
        ],
        'element' => [
            'name'        => 'Elements',
            'description' => 'Element limits require a character to have a certain elemental typing.',
        ],
    ],
];
