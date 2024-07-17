<?php

// config/auth.php
return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
        'company' => [
            'driver' => 'jwt',
            'provider' => 'companies',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\Models\User::class,
        ],
        'companies' => [
            'driver' => 'eloquent',
            'model' => \App\Models\Company::class,
        ],
    ],
];
