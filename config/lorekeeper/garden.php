<?php

return [

    // nav or grid
    'garden_view' => 'grid',

    'garden_object_types' => [
        'seed'       => 'Seed',
        'animal'     => 'Animal',
        'fertiliser' => 'Fertiliser',
        'tool'       => 'Tool',
        'food'       => 'Food',
        // You can add more object types here with custom interactions, just be sure to create the relevant views and logic.
    ],

    'allow_users_to_visit_gardens' => true, // allows users to visit other users' gardens
    'allow_users_to_water_gardens' => true, // allows users to water other users' gardens
    'user_guest_water_limit'       => 5, // number of times a guest user can water another user's garden per day, set to 0 for unlimited
];
