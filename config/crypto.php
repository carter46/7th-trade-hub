<?php

/**
 * CoinGecko integration shared by CryptoPriceService and the public /exchange page.
 * Logo URLs mirror the evergreen_Prime / Bloombit crypto-config pattern.
 */
return [
    'api_base' => env('COINGECKO_API_BASE', 'https://api.coingecko.com/api/v3'),

    /** Symbol (BTC) => CoinGecko id */
    'assets' => [
        'BTC' => 'bitcoin',
        'ETH' => 'ethereum',
        'USDT' => 'tether',
        'SOL' => 'solana',
        'BNB' => 'binancecoin',
    ],

    /** CoinGecko id => official CDN logo */
    'logos' => [
        'bitcoin' => 'https://assets.coingecko.com/coins/images/1/large/bitcoin.png',
        'ethereum' => 'https://assets.coingecko.com/coins/images/279/large/ethereum.png',
        'tether' => 'https://assets.coingecko.com/coins/images/325/large/Tether.png',
        'binancecoin' => 'https://assets.coingecko.com/coins/images/825/large/bnb-icon2_2x.png',
        'solana' => 'https://assets.coingecko.com/coins/images/4128/large/solana.png',
    ],

    'cache_ttl_seconds' => (int) env('COINGECKO_CACHE_TTL', 60),
];
