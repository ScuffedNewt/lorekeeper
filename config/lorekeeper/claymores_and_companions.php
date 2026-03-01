<?php

return [

    'levels' => [
        // the experience ID that is used for leveling up characters and users
        'experience_id' => [
            'users'      => 1,
            'characters' => 1,
        ],
    ],

    'stat_points' => [
        'general_id' => null, // the ID of the general stat point that is used for leveling up characters and users
    ],

    'visibility_settings' => [
        'user_levels'       => 0,
        'character_levels'  => 0,
        'character_classes' => 0,
        'character_skills'  => 0,
        'character_stats'   => 0,
        'weapons'           => 0,
        'gear'              => 0,
    ],
];
