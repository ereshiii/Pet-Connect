<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayMongo Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for PayMongo Philippines integration.
    | You can find your API keys in your PayMongo dashboard.
    |
    */

    'public_key' => env('PAYMONGO_PUBLIC_KEY'),
    'secret_key' => env('PAYMONGO_SECRET_KEY'),
    'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | PayMongo API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for PayMongo API. This should not be changed unless
    | PayMongo changes their API endpoint.
    |
    */
    'base_url' => 'https://api.paymongo.com/v1',

    /*
    |--------------------------------------------------------------------------
    | Supported Payment Methods
    |--------------------------------------------------------------------------
    |
    | Define which payment methods are available for your application.
    |
    */
    'payment_methods' => [
        'card' => [
            'enabled' => true,
            'description' => 'Credit/Debit Cards',
            'fee_percentage' => 3.5,
            'fee_fixed' => 15.00,
        ],
        'gcash' => [
            'enabled' => true,
            'description' => 'GCash',
            'fee_percentage' => 2.5,
            'fee_fixed' => 15.00,
        ],
        'grabpay' => [
            'enabled' => true,
            'description' => 'GrabPay',
            'fee_percentage' => 2.5,
            'fee_fixed' => 15.00,
        ],
        'paymaya' => [
            'enabled' => true,
            'description' => 'PayMaya',
            'fee_percentage' => 3.5,
            'fee_fixed' => 15.00,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for payments. PayMongo Philippines uses PHP.
    |
    */
    'currency' => 'PHP',

    /*
    |--------------------------------------------------------------------------
    | Test Mode
    |--------------------------------------------------------------------------
    |
    | When in test mode, PayMongo will use test keys and no real transactions
    | will be processed. Set this to false in production.
    |
    */
    'test_mode' => env('APP_ENV') !== 'production',

    /*
    |--------------------------------------------------------------------------
    | Mock Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, all PayMongo calls will be simulated without making real
    | API requests. Perfect for development without PayMongo account.
    | Automatically enabled if API keys are not configured.
    |
    */
    'mock_mode' => env('PAYMONGO_MOCK_MODE', true),
];