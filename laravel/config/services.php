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

    'payment' => [
        'mode' => env('PAYMENT_MODE', 'test'), // test или live
        'gateway' => env('PAYMENT_GATEWAY', 'robokassa'),
    ],

        'robokassa' => [
            'merchant_login' => env('ROBOKASSA_MERCHANT_LOGIN'),
            'password_1' => env('ROBOKASSA_PASSWORD_1'),
            'password_2' => env('ROBOKASSA_PASSWORD_2'),
            'success_url' => env('ROBOKASSA_SUCCESS_URL', env('APP_URL') . '/payment/success'),
            'fail_url' => env('ROBOKASSA_FAIL_URL', env('APP_URL') . '/payment/failed'),
        ],

        'yookassa' => [
            'shop_id' => env('YOOKASSA_SHOP_ID'),
            'secret_key' => env('YOOKASSA_SECRET_KEY'),
            'success_url' => env('YOOKASSA_SUCCESS_URL', env('APP_URL') . '/payment/success'),
            'fail_url' => env('YOOKASSA_FAIL_URL', env('APP_URL') . '/payment/failed'),
        ],

    ];
