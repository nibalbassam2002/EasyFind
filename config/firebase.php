<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Project Configuration
    |--------------------------------------------------------------------------
    |
    | This is the configuration for the Firebase project that will be used
    | when accessing Firebase services.
    |
    | You can get the credentials from the Google Cloud Console.
    |
    | https://console.firebase.google.com/project/_/settings/serviceaccounts/adminsdk
    |
    */
    'project_id' => env('FIREBASE_PROJECT_ID'), // e.g. "your-project-id"

    /*
    |--------------------------------------------------------------------------
    | Firebase Service Account
    |--------------------------------------------------------------------------
    |
    | The credentials of a service account can be used to authenticate with
    | the Admin SDKs.
    |
    | The path to the credentials file can be specified as a string or an
    | absolute path.
    |
    | If you are using a different credentials file for different
    | environments, you can use the `FIREBASE_CREDENTIALS`
    | environment variable.
    |
    */
    'credentials' => base_path(env('FIREBASE_CREDENTIALS')),

    /*
    |--------------------------------------------------------------------------
    | Firebase Realtime Database
    |--------------------------------------------------------------------------
    */
    'database' => [
        'url' => env('FIREBASE_DATABASE_URL'), // e.g. "https://your-project.firebaseio.com"
    ],

    /*
    |--------------------------------------------------------------------------
    | Dynamic Links
    |--------------------------------------------------------------------------
    */
    'dynamic_links' => [
        'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloud Storage
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'default_bucket' => env('FIREBASE_STORAGE_BUCKET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | The cache store to use for caching.
    |
    | Supported: "array", "file", "database", "redis", "memcached"
    |
    */
    'cache_store' => env('FIREBASE_CACHE_STORE', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | The log channels to use for logging.
    |
    | You can use any of the channels defined in `config/logging.php`.
    |
    | If you want to use the default log channel, you can set this to `null`.
    |
    */
    'logging' => [
        'channels' => env('FIREBASE_LOG_CHANNELS')
            ? explode(',', env('FIREBASE_LOG_CHANNELS'))
            : null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Debugging
    |--------------------------------------------------------------------------
    |
    | If set to true, the factory will log debug messages.
    |
    | You can also use the `FIREBASE_DEBUG` environment variable.
    |
    */
    'debug' => env('FIREBASE_DEBUG', false),
];