<?php

return [
    // Banned IPs may log in but may not register. Show guests a site-wide warning.
    'show_banned_ip_warning' => true,

    // Optional proxy detection; requires trustip/trustip and an API key when enabled.
    'trustip' => [
        'enabled' => false,
        'api_key' => env('TRUSTIP_API_KEY'),
    ],
];
