<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CSRF Protection
    |--------------------------------------------------------------------------
    |
    | This option determines whether CSRF protection is enabled.
    |
    */
    'enabled' => env('CSRF_ENABLED', true),
    
    /*
    |--------------------------------------------------------------------------
    | CSRF Token Name
    |--------------------------------------------------------------------------
    |
    | The name of the CSRF token parameter.
    |
    */
    'token_name' => '_token',
    
    /*
    |--------------------------------------------------------------------------
    | CSRF Token Lifetime
    |--------------------------------------------------------------------------
    |
    | The number of minutes the CSRF token is valid.
    | Set to 0 for session lifetime.
    |
    */
    'lifetime' => 120,
    
    /*
    |--------------------------------------------------------------------------
    | Excluded URIs
    |--------------------------------------------------------------------------
    |
    | URIs that should be excluded from CSRF protection.
    |
    */
    'except' => [
        // 'api/*',
        // 'webhook/*',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | HTTP Headers
    |--------------------------------------------------------------------------
    |
    | Headers that can contain the CSRF token.
    |
    */
    'headers' => [
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | SameSite Cookie Setting
    |--------------------------------------------------------------------------
    |
    | The SameSite attribute for the CSRF cookie.
    | Options: 'lax', 'strict', 'none', or null
    |
    */
    'same_site' => 'lax',
    
    /*
    |--------------------------------------------------------------------------
    | Secure Cookie
    |--------------------------------------------------------------------------
    |
    | Whether the CSRF cookie should only be sent over HTTPS.
    |
    */
    'secure' => env('APP_SSL', false),
];