<?php

return [
    'seed' => [
        'type'            => 'item', // can be anything
        'is_consumed'     => true,
        'place_word'      => 'Plant',
        'action_word'     => 'Water', // should be a word that can end in 'ing' e.g. verb
        'action_icon'     => 'fas fa-water',
        'action_cooldown' => 24, // in hours, only applies if unique cooldown is not set
        'finish_word'     => 'Harvest', // should be a word that can end in 'ing' e.g. verb
        'finish_icon'     => 'fas fa-leaf',
        'is_die_enabled'  => true, // if true, the plant can die if not watered in specified time
        'die_time'        => 24, // in hours, only applies if can_die is true
        'plot_title'      => 'Plot', // Used on display for individual plots
        'max_quality'     => 5, // max quality of fertilisers and seeds, set to false to disable
        'has_seasons'     => true, // if true, the plant will only grow in certain seasons if set
    ],
    'barn' => [
        'type'            => 'item', // can be pet or item
        'is_consumed'     => false,
        'place_word'      => 'Place',
        'action_word'     => 'Feed',
        'action_icon'     => 'fas fa-hamburger',
        'action_cooldown' => 24,
        'finish_word'     => 'Collect',
        'finish_icon'     => 'fas fa-carrot',
        'is_die_enabled'  => false, // don't set this to true, it won't work (you monster)
        'plot_title'      => 'Barn Stable',
    ],
    'coop' => [
        'type'            => 'item',
        'is_consumed'     => false,
        'place_word'      => 'Place',
        'action_word'     => 'Feed',
        'action_icon'     => 'fas fa-pizza-slice',
        'action_cooldown' => 24,
        'finish_word'     => 'Collect',
        'finish_icon'     => 'fas fa-egg',
        'is_die_enabled'  => false, // don't set this to true, it won't work (you monster)
        'plot_title'      => 'Coop Nest',
    ]
];
