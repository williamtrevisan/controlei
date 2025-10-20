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

    'categorizer' => [
        'url' => env('CATEGORIZER_SERVICE_URL', 'http://categorizer:8080'),
    ],

    'lab' => [
        'url' => env('LAB_SERVICE_URL', 'http://training:5000'),
        'token' => env('LAB_API_TOKEN'),
    ],

    'woovi' => [
        'url' => env('WOOVI_API_URL', 'https://api.woovi-sandbox.com/api/v1'),
        'client' => [
            'id' => env('WOOVI_API_CLIENT_ID', 'Client_Id_ef6fa174-d5fe-42c7-949e-58e7143ccc03'),
        ],
        'key' => env('WOOVI_API_KEY'),
        'webhook_secret' => env('WOOVI_WEBHOOK_SECRET'),
    ],

];
