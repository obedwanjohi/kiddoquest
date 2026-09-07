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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'intasend' => [
        'publishable_key' => env('INTASEND_PUBLISHABLE_KEY'),
        'secret_key'      => env('INTASEND_SECRET_KEY'),
        'test_mode'       => env('INTASEND_TEST_MODE', true),
    ],

    'mpesa' => [
        'consumer_key'    => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'shortcode'       => env('MPESA_SHORTCODE', '174379'),
        'passkey'         => env('MPESA_PASSKEY'),
        'env'             => env('MPESA_ENV', 'sandbox'),
        'tx_type'         => env('MPESA_TX_TYPE', 'CustomerPayBillOnline'),
    ],

    'supabase' => [
        // The project ref is not a secret; the service-role key is and must only live in .env.
        'project_ref' => env('SUPABASE_PROJECT_REF', 'hxxxmizzuddcxmufrsbr'),
        'service_key' => env('SUPABASE_SERVICE_KEY'),
        'bucket'      => env('SUPABASE_STORAGE_BUCKET', 'media'),
    ],

    'groq' => [
        'key' => env('GROQ_API_KEY'),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
    ],

];
