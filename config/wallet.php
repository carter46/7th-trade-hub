<?php

return [
    'platform_crypto_address' => env('WALLET_PLATFORM_CRYPTO_ADDRESS', 'TBD-CONTACT-SUPPORT'),
    'default_provider' => env('WALLET_PROVIDER', 'manual'),
    'crypto_quote_minutes' => 15,
    'default_confirmations' => [
        'bitcoin' => 2,
        'btc' => 2,
        'ethereum' => 12,
        'erc20' => 12,
        'eth' => 12,
        'tron' => 20,
        'trc20' => 20,
        'solana' => 32,
        'sol' => 32,
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed withdrawal banks (Monnify name enquiry / payouts)
    |--------------------------------------------------------------------------
    | Matched case-insensitively against Monnify bank names. Heritage Bank is
    | excluded (license revoked). Duplicate user labels are deduped by code.
    */
    'allowed_bank_patterns' => [
        'access bank',
        'citibank',
        'ecobank',
        'fidelity bank',
        'first bank',
        'fcmb',
        'first city monument',
        'globus bank',
        'guaranty trust',
        'gtbank',
        'jaiz bank',
        'keystone bank',
        'lotus bank',
        'optimus bank',
        'parallex bank',
        'polaris bank',
        'premium trust bank',
        'providus bank',
        'stanbic ibtc',
        'sterling bank',
        'suntrust bank',
        'titan trust bank',
        'union bank',
        'united bank for africa',
        'uba',
        'unity bank',
        'wema bank',
        'zenith bank',
        '9psb',
        '9 payment service bank',
        '9mobile',
        'hope payment service bank',
        'moneymaster psb',
        'palmpay',
        'opay',
        'moniepoint',
        'kuda',
    ],

    'excluded_bank_patterns' => [
        'heritage bank',
    ],
];
