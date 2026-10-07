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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'spaces' => [
        'key' => env('TELEMUSIC_SPACES_KEY', env('AWS_ACCESS_KEY_ID')),
        'secret' => env('TELEMUSIC_SPACES_SECRET', env('AWS_SECRET_ACCESS_KEY')),
        'bucket' => env('TELEMUSIC_SPACES_BUCKET', env('AWS_BUCKET')),
        'endpoint' => env('TELEMUSIC_SPACES_ENDPOINT', env('AWS_ENDPOINT')),
        'signing_region' => env('TELEMUSIC_SPACES_SIGNING_REGION', env('AWS_SIGNING_REGION', 'us-east-1')),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'spotify_scraper' => [
        'key' => env('RAPIDAPI_SPOTIFY_SCRAPER_KEY'),
        'host' => env('RAPIDAPI_SPOTIFY_SCRAPER_HOST', 'spotify-scraper.p.rapidapi.com'),
        'base_url' => env('RAPIDAPI_SPOTIFY_SCRAPER_BASE_URL', 'https://spotify-scraper.p.rapidapi.com'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'base_url' => env('PAYPAL_BASE_URL', 'https://api-m.paypal.com'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'myanmyanpay' => [
        'app_id' => env('MYANMYANPAY_APP_ID'),
        'publishable_key' => env('MYANMYANPAY_PUBLISHABLE_KEY'),
        'secret_key' => env('MYANMYANPAY_SECRET_KEY'),
        'api_base_url' => env('MYANMYANPAY_API_BASE_URL', 'https://ezapi.myanmyanpay.com'),
        'callback_url' => env('MYANMYANPAY_CALLBACK_URL'),
        'sandbox' => env('MYANMYANPAY_SANDBOX', true),
    ],

];
