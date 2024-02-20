<?php
return [

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Secret
    |--------------------------------------------------------------------------
    |
    | This key will be used to sign your JWT authentication tokens. The key
    | should be a random string of 32 characters. You can use the
    | `jwt:secret` Artisan command to generate this key and save it in
    | your configuration file. This will use the `JWT_SECRET` environment
    | variable or fallback to the default value below if not set.
    |
    */

    'secret' => env('JWT_SECRET', 'your-secret-key-here'),

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Keys
    |--------------------------------------------------------------------------
    |
    | The keys that will be used to sign your JWT authentication tokens.
    | These keys should be stored securely. You can use the `jwt:secret`
    | Artisan command to generate these keys and save them in your
    | configuration file. These will use the `JWT_SECRET` environment
    | variable or fallback to the default value below if not set.
    |
    */

    'keys' => [
        'public' => env('JWT_PUBLIC_KEY'),
        'private' => env('JWT_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Algorithm
    |--------------------------------------------------------------------------
    |
    | This option controls the algorithm that will be used to sign JWT
    | authentication tokens. Supported algorithms include HMAC with SHA-256
    | and RSA. You should adjust this setting based on your security
    | requirements. By default, HMAC with SHA-256 is used.
    |
    */

    'algorithm' => env('JWT_ALGORITHM', 'HS256'),

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication TTL
    |--------------------------------------------------------------------------
    |
    | This option controls the time-to-live (TTL) for JWT authentication tokens.
    | Tokens will expire after this period of time, and users will need to
    | re-authenticate. The value should be in seconds.
    |
    */

    'ttl' => env('JWT_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Refresh TTL
    |--------------------------------------------------------------------------
    |
    | This option controls the time-to-live (TTL) for JWT refresh tokens.
    | Refresh tokens will expire after this period of time, and users will
    | need to re-authenticate to obtain a new access token. The value
    | should be in seconds.
    |
    */

    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

];
