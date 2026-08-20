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
            'mode_text'   => [
                'place'       => 'Plant',
                'place_past'  => 'planted',
                'tend'        => 'Water',
                'tend_plural' => 'Waterings',
                'tend_past'   => 'Watered',
                'collect'     => 'Harvest',
            ],
        ],
        'animal'     => [
            'displayName' => 'Animal',
            'removed'     => false,
            'type'        => 'placeable',
            'mode_text'   => [
                'place'       => 'Place',
                'place_past'  => 'placed',
                'tend'        => 'Feed',
                'tend_plural' => 'Feedings',
                'tend_past'   => 'Fed',
                'collect'     => 'Collect',
            ],
        ],
        // applied BEFORE placing a seed
        'fertiliser' => [
            'displayName' => 'Fertiliser',
            'type'        => 'modifier',
            'mode_text'   => [
                'place' => 'Apply',
            ],
        ],
        // applied before placing a seed or animal; degradable items lose uses
        // according to the Garden activity configured on their Degradable tag
        'tool'       => [
            'displayName' => 'Tool',
            'type'        => 'modifier',
            'mode_text'   => [
                'place' => 'Use',
            ],
        ],
        // You can add more object types here with custom interactions, just be sure to create the relevant views and logic.
    ],

    'allow_users_to_visit_gardens' => true, // allows users to visit other users' gardens
    'allow_users_to_water_gardens' => true, // allows users to water other users' gardens
    'user_guest_water_limit'       => 5, // number of times a guest user can water another user's garden TOTAL per day, set to 0 for unlimited
];
