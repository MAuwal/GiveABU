<?php

return [
    'security' => [
        'seed_passwords' => [
            'admin' => env('SEED_ADMIN_PASSWORD'),
            'finance' => env('SEED_FINANCE_PASSWORD'),
            'executive' => env('SEED_EXECUTIVE_PASSWORD'),
        ],
        'frontend_url' => env('FRONTEND_URL', env('APP_URL')),
        'reset_callback_urls' => array_filter(array_map('trim', explode(',', env('RESET_CALLBACK_URLS', '')))),
    ],

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

    // Ozeki SMS Gateway Configuration
    'ozeki' => [
        'username' => env('OZEKI_USERNAME'),
        'password' => env('OZEKI_PASSWORD'),
        'api_url' => env('OZEKI_API_URL', 'http://127.0.0.1:9509/api?action=rest'),
        'enabled' => env('SMS_VERIFICATION_ENABLED', true),
    ],

    'kudi' => [
        'token' => env('KUDI_SMS_KEY'),
        'url' => env('KUDI_SMS_URL', 'https://my.kudisms.net/api/intcomposesms'),
    ],

    // Squad Payment Configuration
    'squad' => [
        // Exact callback URL bases, including approved mobile deep links.
        'callback_urls' => array_filter(array_map('trim', explode(',', env('SQUAD_CALLBACK_URLS', '')))),
        'secret_key' => env('SQUAD_SECRET_KEY', ''),
        'base_url'   => env('SQUAD_API_URL', env('SQUAD_BASE_URL', 'https://api-d.squadco.com')),
    ],

    // Interswitch Payment Configuration
    'interswitch' => [
        'merchant_code' => env('INTERSWITCH_MERCHANT_CODE', ''),
        'pay_item_id' => env('INTERSWITCH_PAY_ITEM_ID', ''),
        'secret_key' => env('INTERSWITCH_SECRET_KEY', ''),
        'base_url' => env('INTERSWITCH_API_URL', 'https://qa.interswitchng.com'),
        'checkout_url' => env('INTERSWITCH_CHECKOUT_URL', 'https://qa.interswitchng.com/collections/w/pay'),
        'currency_code' => env('INTERSWITCH_CURRENCY_CODE', '566'),
    ],

    // Paystack Configuration
    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
        'url' => env('PAYSTACK_URL', 'https://api.paystack.co'),
    ],

    // Google OAuth Configuration
    'google' => [
        'client_id'          => env('GOOGLE_CLIENT_ID'),
        'client_secret'      => env('GOOGLE_CLIENT_SECRET'),
        'redirect'           => env('GOOGLE_REDIRECT_URI'),
        // Android and iOS apps generate tokens with a different aud than the web client.
        // List ALL allowed client IDs as a comma-separated string in the env.
        // e.g. GOOGLE_ALLOWED_CLIENT_IDS=web_id.apps.googleusercontent.com,android_id.apps.googleusercontent.com
        'allowed_client_ids' => env('GOOGLE_ALLOWED_CLIENT_IDS', env('GOOGLE_CLIENT_ID')),
    ],

];
