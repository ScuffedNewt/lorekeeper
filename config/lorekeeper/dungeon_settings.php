<?php

return [
    // model type - this is so if you wan't to use some other quantifier
    // make sure it's the full trace
    'model' => 'App\Models\Stat\Stat',
    // sometimes the query will need to change depending on the type of
    // model you're looking for

    'show_health_bar_on_characters' => true,
    // regen settings
    'regen_health'                  => true,
    'regen_health_amount'           => 10,
    'regen_health_time'             => 60, // in minutes, will run at every hour + minutes * n

    // resets character's stats on transfer TODO
    'reset_stats_on_transfer' => true,

    // by default, these should be the stat ids
    'stats' => [
        'health'  => [
            'id'        => 1,
            'base_stat' => 10, // if there is no health stat... etc
            'max'       => true,
        ],
        'attack'  => [
            'id'        => null,
            'base_stat' => 5,
        ],
        'defense' => [
            'id'        => null,
            'base_stat' => 5,
        ],
        'speed'  => [
            'id'        => null,
            'base_stat' => 5,
        ],
    ],

    // if this is set to true, damage taken in fights will persist after the fight
    // this is useful for when you want to keep track of how much damage a character has taken or avail of healing potions etc
    'damage_persist' => true,

    // whether or not to show users the exact amount of damage dealt
    // this is useful for when you're dealing with a lot of damage
    // or when dealing with boss fights
    'show_exact_damage' => true,

    // minimum amount of damage done in a fight, set to 0 to disable
    'min_damage' => 1,

    // 0: gives the exp to the user, 1: gives the exp to the selected character
    // personally recommend using 1
    'exp_type' => 1,

    // allows FTO / Non Owner users to use NPCs, and set the NPC character category
    'npcs' => [
        'enabled'            => true,
        'category_or_rarity' => 'category',
        // this should be the category code or rarity name
        'code' => 'npc',
        // if this is set to true, the ids array will be used instead of the category or rarity
        'use_ids' => true,
        // array of character ids that can be used as NPCs if your site does not define NPCs as a category or rarity
        'ids' => [
            7, 8,
        ],
    ],

    'arena_reset' => 'weekly',
];
