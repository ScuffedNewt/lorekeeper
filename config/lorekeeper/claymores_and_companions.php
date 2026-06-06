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
        'user_levels'       => 1,
        'character_levels'  => 1,
        'character_classes' => 0,
        'character_skills'  => 1,
        'character_stats'   => 1,
        'weapons'           => 1,
        'gear'              => 1,
    ],
];
