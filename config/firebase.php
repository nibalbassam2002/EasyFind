<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Project Configuration
    |--------------------------------------------------------------------------
    */
    'project_id' => env('FIREBASE_PROJECT_ID'), // اقرأ من .env

    /*
    |--------------------------------------------------------------------------
    | Firebase Service Account
    |--------------------------------------------------------------------------
    |
    | الـ credentials الآن تقرأ المسار الكامل من ملف .env
    |
    */
'credentials' => storage_path('app/firebase_credentials.json'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Realtime Database
    |--------------------------------------------------------------------------
    */
    'database' => [
        'url' => env('FIREBASE_DATABASE_URL'), // اقرأ من .env
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloud Storage
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'default_bucket' => env('FIREBASE_STORAGE_BUCKET'), // اقرأ من .env
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching, Logging, Debugging (لا تحتاج لتغيير)
    |--------------------------------------------------------------------------
    */
    'cache_store' => env('FIREBASE_CACHE_STORE', 'file'),
    'logging' => [
        'channels' => env('FIREBASE_LOG_CHANNELS')
            ? explode(',', env('FIREBASE_LOG_CHANNELS'))
            : null,
    ],
    'debug' => env('FIREBASE_DEBUG', false),

    'dynamic_links' => [
        'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
    ],
];