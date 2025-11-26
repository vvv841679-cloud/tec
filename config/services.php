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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'masterqr' => [
        'base_url' => env('MASTERQR_BASE_URL', 'https://masterqr.pagofacil.com.bo/api/services/v2'),
        'token_service' => env('MASTERQR_TOKEN_SERVICE'),
        'token_secret' => env('MASTERQR_TOKEN_SECRET'),
        'client_code' => env('MASTERQR_CLIENT_CODE'),
        'callback_url' => env('MASTERQR_CALLBACK_URL'),
        'test_amount' => env('MASTERQR_TEST_AMOUNT', 0.10), // Monto fijo para demos
    ],

];
