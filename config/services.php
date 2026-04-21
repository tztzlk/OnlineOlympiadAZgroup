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

    'support' => [
        'email' => env('SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS')),
        'phone' => env('SUPPORT_PHONE', '+7 (777) 000-00-00'),
    ],

    'kaspi' => [
        'payment_url' => env('KASPI_PAYMENT_URL', 'https://kaspi.kz/pay/_gate?action=service_with_subservice&service_id=3025&subservice_id=22909&region_id=19'),
        'callback_secret' => env('KASPI_CALLBACK_SECRET', ''),
    ],

    'webhooks' => [
        'yookassa_secret' => env('YOOKASSA_WEBHOOK_SECRET'),
        'stripe_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'telegram_secret_token' => env('TELEGRAM_WEBHOOK_SECRET_TOKEN'),
    ],

];
