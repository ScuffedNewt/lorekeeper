<?php

return [
    'limit_types' => [
        'prompt' => [
            'name'        => 'Prompts',
            'description' => 'Prompt limits require a user to have submitted to the specified prompt a certain number of times.',
            'debitable'   => false,
            'countable'   => true,
        ],
        'item' => [
            'name'        => 'Items',
            'description' => 'Item limits require a user to have a certain number of items in their inventory.',
            'debitable'   => true,
            'countable'   => true,
        ],
        'currency' => [
            'name'        => 'Currency',
            'description' => 'Currency limits require a user to have a certain amount of currency.',
            'debitable'   => true,
            'countable'   => true,
        ],
        'dynamic' => [
            'name'        => 'Dynamic',
            'description' => 'Dynamic limits require a user to meet a certain condition. The condition is evaluated at runtime.',
            'debitable'   => false,
            'countable'   => false,
        ],
        'element' => [
            'name'        => 'Elements',
            'description' => 'Element limits require a character to have a certain elemental typing.',
            'debitable'   => false,
            'countable'   => false,
        ],
    ] +
    (config('lorekeeper.claymores_and_companions.visibility_settings.character_classes') ? [
        'class' => [
            'name'        => 'Classes',
            'description' => 'Class limits require a character to have a certain class.',
            'debitable'   => false,
            'countable'   => false,
        ],
    ] : []) +
    (config('lorekeeper.claymores_and_companions.visibility_settings.character_levels') ? [
        'character_level' => [
            'name'        => 'Character Levels',
            'description' => 'Level limits require a character to have a certain level.',
            'debitable'   => false,
            'countable'   => false,
        ],
    ] : []) +
    (config('lorekeeper.claymores_and_companions.visibility_settings.user_levels') ? [
        'user_level' => [
            'name'        => 'User Levels',
            'description' => 'Level limits require a user to have a certain level.',
            'debitable'   => false,
            'countable'   => false,
        ],
    ] : []) +
    (config('lorekeeper.claymores_and_companions.visibility_settings.character_stats') ? [
        'stat' => [
            'name'        => 'Stats',
            'description' => 'Stat limits require a character to have a certain amount of a specific stat.',
            'debitable'   => false,
            'countable'   => true,
        ],
    ] : []),
];