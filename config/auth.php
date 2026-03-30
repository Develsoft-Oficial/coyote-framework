<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'input_key' => 'api_token',
            'storage_key' => 'api_token',
            'hash' => false,
        ],

        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \Coyote\Auth\Models\User::class,
        ],

        'admins' => [
            'driver' => 'database',
            'table' => 'admins',
            'identifier' => 'id',
        ],

        'legacy' => [
            'driver' => 'database',
            'table' => 'legacy_users',
            'identifier' => 'user_id',
            'connection' => 'legacy',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],

        'admins' => [
            'provider' => 'admins',
            'table' => 'admin_password_resets',
            'expire' => 30,
            'throttle' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => 10800,

    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the session configuration for authentication guards.
    | These settings are used by session-based authentication guards.
    |
    */

    'session' => [
        'driver' => 'file',
        'lifetime' => 120,
        'expire_on_close' => false,
        'encrypt' => false,
        'files' => storage_path('framework/sessions'),
        'connection' => null,
        'table' => 'sessions',
        'store' => null,
        'lottery' => [2, 100],
        'cookie' => 'coyote_session',
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'http_only' => true,
        'same_site' => 'lax',
    ],

    /*
    |--------------------------------------------------------------------------
    | Remember Me Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the "remember me" functionality for your
    | application. The remember me cookie stores a token that can be used
    | to automatically authenticate users without requiring them to provide
    | their credentials again.
    |
    */

    'remember' => [
        'cookie' => 'coyote_remember',
        'expire' => 2628000, // 5 years in minutes
        'domain' => null,
        'secure' => false,
        'http_only' => true,
        'same_site' => 'lax',
    ],

    /*
    |--------------------------------------------------------------------------
    | Throttling Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the throttling settings for authentication
    | attempts. You may change these defaults as needed.
    |
    */

    'throttling' => [
        'enabled' => true,
        'max_attempts' => 5,
        'decay_minutes' => 1,
        'key_prefix' => 'login_attempts_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Factor Authentication
    |--------------------------------------------------------------------------
    |
    | Here you may configure multi-factor authentication settings.
    |
    */

    'mfa' => [
        'enabled' => false,
        'driver' => 'totp', // totp, sms, email
        'issuer' => 'Coyote Framework',
        'window' => 1, // Time window for TOTP validation
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Authentication
    |--------------------------------------------------------------------------
    |
    | Here you may configure social authentication providers.
    |
    */

    'social' => [
        'enabled' => false,
        'providers' => [
            // 'github' => [
            //     'client_id' => env('GITHUB_CLIENT_ID'),
            //     'client_secret' => env('GITHUB_CLIENT_SECRET'),
            //     'redirect' => env('GITHUB_REDIRECT_URI'),
            // ],
            // 'google' => [
            //     'client_id' => env('GOOGLE_CLIENT_ID'),
            //     'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            //     'redirect' => env('GOOGLE_REDIRECT_URI'),
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Authentication Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may register custom authentication drivers that extend the
    | built-in authentication system.
    |
    */

    'custom_drivers' => [
        // 'ldap' => \App\Auth\Drivers\LdapDriver::class,
        // 'oauth' => \App\Auth\Drivers\OAuthDriver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Events
    |--------------------------------------------------------------------------
    |
    | Here you may configure which events are fired during authentication.
    |
    */

    'events' => [
        'login' => true,
        'logout' => true,
        'attempting' => true,
        'failed' => true,
        'locked' => true,
    ],
];