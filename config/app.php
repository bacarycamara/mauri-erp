<?php

return [

    /*
    |--------------------------------------------------------------------------
    | APPLICATION CORE
    |--------------------------------------------------------------------------
    */

    'name' => env('APP_NAME', 'MauriERP'),

    'product_name' => 'MauriERP',

    'product_version' => '1.0.0',

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    'timezone' => 'Africa/Nouakchott',

    'locale' => 'fr',

    'fallback_locale' => 'fr',

    'faker_locale' => 'fr_FR',

    /*
    |--------------------------------------------------------------------------
    | ENCRYPTION
    |--------------------------------------------------------------------------
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | MAINTENANCE
    |--------------------------------------------------------------------------
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | =======================
    |  MAURIERP BRANDING
    | =======================
    |--------------------------------------------------------------------------
    | Ces valeurs NE DOIVENT PAS être supprimées.
    | Elles garantissent la signature éditeur.
    |--------------------------------------------------------------------------
    */

    'vendor' => [
        'name' => 'MauriERP',
        'author' => 'BK Camara',
        'website' => 'https://maurierp.mr',
        'email' => 'contact@maurierp.mr',
        'logo' => 'images/maurierp-logo.png',
        'copyright' => '© '.date('Y').' MauriERP. Tous droits réservés.',
    ],

    /*
    |--------------------------------------------------------------------------
    | LICENSING SYSTEM (Préparation future)
    |--------------------------------------------------------------------------
    */

    'license' => [
        'enabled' => true,
        'grace_days' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | SaaS MODE (Préparation multi-entreprise future)
    |--------------------------------------------------------------------------
    */

    'saas' => [
        'enabled' => false,
    ],

];