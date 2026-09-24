<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Item tags
    |--------------------------------------------------------------------------
    |
    | This is a list of tags that can be attached to items.
    | Add tags here to make them selectable in the admin panel.
    | The key must be unique, but names do not have to be.
    |
    */

    'box'  => [
        'name'             => 'Box',
        'text_color'       => '#ffffff',
        'background_color' => '#f6993f',
        'description'      => 'This item can be opened for a preset reward.',
    ],

    'slot' => [
        'name'             => 'Slot',
        'text_color'       => '#ffffff',
        'background_color' => '#1fd1a7',
        'description'      => 'This item can be used to create an MYO slot.',
    ],

    'splice' => [
        'name'             => 'Splice',
        'text_color'       => '#ffffff',
        'background_color' => '#a69bc6',
    ],

    'potion' => [
        'name'             => 'Potion',
        'text_color'       => '#ffffff',
        'background_color' => '#f540a3',
    ],

    // pokemon ftw
    // if you want to change this, just edit the 'name' part.
    'rare_candy' => [
        'name'             => 'Rare Candy',
        'text_color'       => '#ffffff',
        'background_color' => '#96afdb',
    ],

    'coupon' => [
        'name'             => 'Coupon',
        'text_color'       => '#ffffff',
        'background_color' => '#ff5ca8',
        'description'      => 'This item can be redeemed at an eligible shop for a discount.',
    ],

    'elemental_potion' => [
        'name'             => 'Elemental Potion',
        'text_color'       => '#f5f3d3',
        'background_color' => '#468a82',
    ],

    'buff' => [
        'name'             => 'Buff',
        'text_color'       => '#ffffff',
        'background_color' => '#a4b88d',
    ],

    'cure' => [
        'name'             => 'Cure',
        'text_color'       => '#ffffff',
        'background_color' => '#b4676b',
    ],
];
