<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Sidebar Links
    |--------------------------------------------------------------------------
    |
    | Admin panel sidebar links.
    | Add links here to have them show up in the admin panel.
    | Users that do not have the listed power will not be able to
    | view the links in that section.
    |
    */

    'Admin'      => [
        'power' => 'admin',
        'links' => [
            [
                'name' => 'User Ranks',
                'url'  => 'admin/users/ranks',
            ],
            [
                'name' => 'Admin Logs',
                'url'  => 'admin/admin-logs',
            ],
            [
                'name' => 'Staff Reward Settings',
                'url'  => 'admin/staff-reward-settings',
            ],
        ],
    ],
    'Reports'    => [
        'power' => 'manage_reports',
        'links' => [
            [
                'name' => 'Report Queue',
                'url'  => 'admin/reports/pending',
            ],
        ],
    ],
    'News' => [
        'power' => 'manage_news',
        'links' => [
            [
                'name' => 'News',
                'url'  => 'admin/news',
            ],
        ],
    ],
    'Sales' => [
        'power' => 'manage_sales',
        'links' => [
            [
                'name' => 'Sales',
                'url'  => 'admin/sales',
            ],
        ],
    ],
    'Pages'       => [
        'power' => 'edit_pages',
        'links' => [
            [
                'name' => 'Pages',
                'url'  => 'admin/pages',
            ],
        ],
    ],
    'Users'      => [
        'power' => 'edit_user_info',
        'links' => [
            [
                'name' => 'User Index',
                'url'  => 'admin/users',
            ],
            [
                'name' => 'Invitation Keys',
                'url'  => 'admin/invitations',
            ],
        ],
    ],
    'Queues'     => [
        'power' => 'manage_submissions',
        'links' => [
            [
                'name' => 'Gallery Submissions',
                'url'  => 'admin/gallery/submissions',
            ],
            [
                'name' => 'Gallery Currency Awards',
                'url'  => 'admin/gallery/currency',
            ],
            [
                'name' => 'Prompt Submissions',
                'url'  => 'admin/submissions',
            ],
            [
                'name' => 'Claim Submissions',
                'url'  => 'admin/claims',
            ],
        ],
    ],
    'Grants'     => [
        'power' => 'edit_inventories',
        'links' => [
            [
                'name' => 'Currency Grants',
                'url'  => 'admin/grants/user-currency',
            ],
            [
                'name' => 'Item Grants',
                'url'  => 'admin/grants/items',
            ],
            [
                'name' => 'EXP Grants',
                'url'  => 'admin/grants/exp',
            ],
            [
                'name' => 'Stat Point Grants',
                'url'  => 'admin/grants/points',
            ],
            [
                'name' => 'Pet Grants',
                'url'  => 'admin/grants/pets',
            ],
            [
                'name' => 'Gear Grants',
                'url'  => 'admin/grants/gear',
            ],
            [
                'name' => 'Weapon Grants',
                'url'  => 'admin/grants/weapons',
            ],
            [
                'name' => 'Skill Grants',
                'url'  => 'admin/grants/skills',
            ],
        ],
    ],
    'Masterlist' => [
        'power' => 'manage_characters',
        'links' => [
            [
                'name' => 'Create Character',
                'url'  => 'admin/masterlist/create-character',
            ],
            [
                'name' => 'Create MYO Slot',
                'url'  => 'admin/masterlist/create-myo',
            ],
            [
                'name' => 'Character Transfers',
                'url'  => 'admin/masterlist/transfers/incoming',
            ],
            [
                'name' => 'Character Trades',
                'url'  => 'admin/masterlist/trades/incoming',
            ],
            [
                'name' => 'Design Updates',
                'url'  => 'admin/design-approvals/pending',
            ],
            [
                'name' => 'MYO Approvals',
                'url'  => 'admin/myo-approvals/pending',
            ],
        ],
    ],
    'Data'       => [
        'power' => 'edit_data',
        'links' => [
            [
                'name' => 'Galleries',
                'url'  => 'admin/data/galleries',
            ],
            [
                'name' => 'Character Categories',
                'url'  => 'admin/data/character-categories',
            ],
            [
                'name' => 'Sub Masterlists',
                'url'  => 'admin/data/sublists',
            ],
            [
                'name' => 'Rarities',
                'url'  => 'admin/data/rarities',
            ],
            [
                'name' => 'Species',
                'url'  => 'admin/data/species',
            ],
            [
                'name' => 'Subtypes',
                'url'  => 'admin/data/subtypes',
            ],
            [
                'name' => 'Traits',
                'url'  => 'admin/data/traits',
            ],
            [
                'name' => 'Status Effects',
                'url'  => 'admin/data/status-effects',
            ],
            [
                'name' => 'Shops',
                'url'  => 'admin/data/shops',
            ],
            [
                'name' => 'Currencies',
                'url'  => 'admin/data/currencies',
            ],
            [
                'name' => 'Prompts',
                'url'  => 'admin/data/prompts',
            ],
            [
                'name' => 'Loot Tables',
                'url'  => 'admin/data/loot-tables',
            ],
            [
                'name' => 'Items',
                'url'  => 'admin/data/items',
            ],
            [
                'name' => 'Pets',
                'url'  => 'admin/data/pets',
            ],
            [
                'name' => 'Dynamic Limits',
                'url'  => 'admin/data/limits',
            ],
        ],
    ],
    'Claymores' => [
        'power' => 'edit_claymores',
        'links' => [
            [
                'name' => 'Gear',
                'url'  => 'admin/gear',
            ],
            [
                'name' => 'Weapons',
                'url'  => 'admin/weapons',
            ],
            // [
            //     'name' => 'Attacks',
            //     'url'  => 'admin/attacks',
            // ],
            [
                'name' => 'Character Classes',
                'url'  => 'admin/character-classes',
            ],
            [
                'name' => 'Character Skills',
                'url'  => 'admin/data/skills',
            ],
            [
                'name' => 'Stats',
                'url'  => 'admin/stats',
            ],
            [
                'name' => 'Character Levels',
                'url'  => 'admin/levels/character',
            ],
            [
                'name' => 'User Levels',
                'url'  => 'admin/levels/user',
            ],
            [
                'name' => 'Elements',
                'url'  => 'admin/data/elements',

            ],
        ],
    ],
    'Raffles'    => [
        'power' => 'manage_raffles',
        'links' => [
            [
                'name' => 'Raffles',
                'url'  => 'admin/raffles',
            ],
        ],
    ],
    'Settings'   => [
        'power' => 'edit_site_settings',
        'links' => [
            [
                'name' => 'Site Settings',
                'url'  => 'admin/settings',
            ],
            [
                'name' => 'Site Images',
                'url'  => 'admin/images',
            ],
            [
                'name' => 'File Manager',
                'url'  => 'admin/files',
            ],
            [
                'name' => 'Log Viewer',
                'url'  => 'admin/logs',
            ],
        ],
    ],
];
