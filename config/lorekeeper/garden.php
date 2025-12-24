<?php

return [

    'garden_object_types' => [
        /*
         * do not modify the keys here, as they are used in the code to identify object types
         * you can modify the display names as needed
         * Utilised values:
         *
         * 'displayName' - The name displayed in the admin and user interfaces.
         * 'removed' - Whether the object is removed on harvest, true by default.
         */

        'seed'       => [
            'displayName' => 'Seed',
            'type'        => 'placeable',
        ],
        'animal'     => [
            'displayName' => 'Animal',
            'removed'     => false,
            'type'        => 'placeable',
        ],
        'fertiliser' => [
            'displayName' => 'Fertiliser',
            'type'        => 'modifier',
        ],
        'tool'       => [
            'displayName' => 'Tool',
            'type'        => 'modifier',
        ],
        'food'       => [
            'displayName' => 'Food',
            'type'        => 'modifier',
        ],
        // You can add more object types here with custom interactions, just be sure to create the relevant views and logic.
    ],

    'allow_users_to_visit_gardens' => true, // allows users to visit other users' gardens
    'allow_users_to_water_gardens' => true, // allows users to water other users' gardens
    'user_guest_water_limit'       => 5, // number of times a guest user can water another user's garden per day, set to 0 for unlimited
];
