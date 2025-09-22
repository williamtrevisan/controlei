<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default bank
    |--------------------------------------------------------------------------
    |
    | Defines which bank configuration should be used by default when
    | performing fetch operations. The value must match one of the
    | entries listed in the "banks" section below.
    |
    */

    'bank' => env('BANK', 'itau'),

    /*
    |--------------------------------------------------------------------------
    | Banks
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the banks available for fetch operations.
    | Example configurations are provided, and you are free to add or
    | remove entries as needed for your environment.
    |
    */

    'banks' => [
        'itau' => [
            'base_url' => env('BANK_BASE_URL', 'https://internetpf5.itau.com.br'),
            'agency' => env('BANK_AGENCY'),
            'account' => env('BANK_ACCOUNT'),
            'account_digit' => env('BANK_ACCOUNT_DIGIT'),
            'password' => env('BANK_PASSWORD'),

            /*
            |--------------------------------------------------------------------------
            | Transaction classification patterns
            |--------------------------------------------------------------------------
            |
            | Here you may define regex patterns used to classify transactions based
            | on the raw description text returned by each bank. Each transaction
            | kind maps to one or more patterns, and the first match determines
            | how the transaction will be classified.
            |
            */

            'classifiers' => [
                \App\Actions\Classifiers\CashbackTransactionClassifier::class,
                \App\Actions\Classifiers\FeeTransactionClassifier::class,
                \App\Actions\Classifiers\InvoicePaymentTransactionClassifier::class
            ],

            /*
            |--------------------------------------------------------------------------
            | Days between closing and due Day
            |--------------------------------------------------------------------------
            |
            | Interval in days between the credit card closing date and the due day.
            |
            */

            'closing_due_interval_days' => env('BANK_CLOSING_DUE_INTERVAL_DAYS', 7),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default currency
    |--------------------------------------------------------------------------
    |
    | Defines the default currency for monetary values in your application.
    | Used by the Money value object unless explicitly overridden.
    |
    */

    'currency' => env('BANKLINK_CURRENCY', 'BRL'),
];
