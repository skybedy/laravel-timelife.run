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

    'facebook' => [
        'client_id' => env("FACEBOOK_CLIENT_ID"),
        'client_secret' => env("FACEBOOK_CLIENT_SECRET"),
        'redirect' => env("APP_URL").'/auth/facebook/callback',
        'app_id' => env("FACEBOOK_APP_ID", env("FACEBOOK_CLIENT_ID")),
    ],

    'google' => [
        'client_id' => env("GOOGLE_CLIENT_ID"),
        'client_secret' => env("GOOGLE_CLIENT_SECRET"),
        'redirect' => env("APP_URL").'/auth/google/callback',
    ],

    'strava' => [
        'client_id' => env("STRAVA_CLIENT_ID"),
        'client_secret' => env("STRAVA_CLIENT_SECRET"),
        'redirect' => env("APP_URL").'/auth/strava/callback',
    ],

    'google_analytics' => [
        'id' => env('GOOGLE_ANALYTICS_ID'),
    ],
    
    'stripe' => [
        'key' => env('STRIPE_KEY'),           // pk_live_...
        'secret' => env('STRIPE_SECRET'),     // sk_live_...
        'connect_client_id' => env('STRIPE_CONNECT_CLIENT_ID'), // acct_...
    ],

];
