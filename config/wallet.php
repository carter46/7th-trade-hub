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
];
