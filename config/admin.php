<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Initial administrator
    |--------------------------------------------------------------------------
    |
    | These values are used only when the database does not yet contain an
    | administrator. A production installation must provide its own password.
    |
    */
    'seed' => [
        'first_name' => env('ADMIN_FIRST_NAME', 'Site'),
        'last_name' => env('ADMIN_LAST_NAME', 'Administrator'),
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
