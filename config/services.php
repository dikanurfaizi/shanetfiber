<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'whatsapp' => [
        'base_url' => 'https://wa.me/',
        'ssl_verify' => env('WHATSAPP_SSL_VERIFY', true),
        'timeout' => env('WHATSAPP_TIMEOUT', 10),
        'version' => env('WHATSAPP_VERSION', 'v22.0'),
        'graph_url' => env('WHATSAPP_GRAPH_URL', 'https://graph.facebook.com'),
    ],

    'shanetfiber' => [
        'allow_mikrotik_actions' => env('ALLOW_MIKROTIK_ACTIONS', false),
    ],

    'meta' => [
        'verify_token' => env('META_VERIFY_TOKEN'),
        'app_secret' => env('META_APP_SECRET'),
    ],

];
