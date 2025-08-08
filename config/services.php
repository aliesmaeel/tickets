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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'standingtech' => [
        'token' => env('STANDINGTECH_API_TOKEN'),
    ],

    'fcm' => [
        'project_id' => env('FCM_PROJECT_ID', ''),
        'credentials_file_path' => env('FCM_CREDENTIALS_FILE_PATH', ''), // It must be added to /storage/app folder
    ],
    'hyperpay' => [
        'token' => env('HYPERPAY_TOKEN'),
        'entity_id' => env('HYPERPAY_ENTITY_ID'),
        'currency' => env('HYPERPAY_CURRENCY', 'IQD'),
        'test_url_checkout' => env('HYPERPAY_TEST_URL_CHECKOUT', 'https://test.oppwa.com/v1/checkouts'),
        'test_url_verify' => env('HYPERPAY_TEST_URL_VERIFY', 'https://eu-test.oppwa.com/v3/query'),
        'shopper_result_url' => env('HYPERPAY_SHOPPER_RESULT_URL', 'https://yourdomain.com/payment-result'),
    ],


];
