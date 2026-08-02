<?php

/**
 * CoinGecko + OTC pricing / explorer / wallet pool config for Crypto OTC v1.
 */
return [
    'api_base' => env('COINGECKO_API_BASE', 'https://api.coingecko.com/api/v3'),

    'bybit_spot_base' => env('BYBIT_SPOT_API_BASE', 'https://api.bybit.com'),

    /** Symbol (BTC) => CoinGecko id */
    'assets' => [
        'BTC' => 'bitcoin',
        'ETH' => 'ethereum',
        'USDT' => 'tether',
        'USDC' => 'usd-coin',
        'SOL' => 'solana',
        'BNB' => 'binancecoin',
    ],

    /** Default Bybit spot symbols for coin USD */
    'bybit_symbols' => [
        'BTC' => 'BTCUSDT',
        'ETH' => 'ETHUSDT',
        'USDT' => 'USDTUSDT',
        'USDC' => 'USDCUSDT',
        'SOL' => 'SOLUSDT',
        'BNB' => 'BNBUSDT',
    ],

    /** CoinGecko id => official CDN logo */
    'logos' => [
        'bitcoin' => 'https://assets.coingecko.com/coins/images/1/large/bitcoin.png',
        'ethereum' => 'https://assets.coingecko.com/coins/images/279/large/ethereum.png',
        'tether' => 'https://assets.coingecko.com/coins/images/325/large/Tether.png',
        'usd-coin' => 'https://assets.coingecko.com/coins/images/6319/large/usdc.png',
        'binancecoin' => 'https://assets.coingecko.com/coins/images/825/large/bnb-icon2_2x.png',
        'solana' => 'https://assets.coingecko.com/coins/images/4128/large/solana.png',
    ],

    'cache_ttl_seconds' => (int) env('COINGECKO_CACHE_TTL', 60),

    /**
     * Controlled coin → allowed networks (canonical labels).
     * Admin and allocation must use these values only.
     *
     * @var array<string, list<string>>
     */
    'networks_by_coin' => [
        'BTC' => ['Bitcoin'],
        'ETH' => ['Ethereum'],
        'USDT' => ['TRC20', 'ERC20', 'BEP20', 'Polygon', 'Solana'],
        'USDC' => ['ERC20', 'Polygon', 'Solana', 'Base', 'Arbitrum'],
        'SOL' => ['Solana'],
        'BNB' => ['BEP20'],
    ],

    /** Decimal places for fingerprint rounding */
    'amount_precision' => [
        'BTC' => 8,
        'ETH' => 8,
        'USDT' => 6,
        'USDC' => 6,
        'SOL' => 9,
        'BNB' => 8,
    ],

    /** Max active deposit wallets per coin+network (business rule). */
    'max_active_wallets_per_network' => 5,

    /** Default max concurrent open sell orders per wallet (overridable in OTC settings). */
    'max_orders_per_wallet' => 8,

    /** Fingerprint nudge attempts per wallet before trying the next. */
    'fingerprint_max_nudges' => 50,

    /**
     * Official token contracts (lowercase for EVM; Tron base58 as published).
     * Native coins use null (no contract filter).
     *
     * @var array<string, array<string, string|null>>
     */
    'token_contracts' => [
        'USDT' => [
            'TRC20' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
            'ERC20' => '0xdac17f958d2ee523a2206206994597c13d831ec7',
            'BEP20' => '0x55d398326f99059ff775485246999027b3197955',
            'Polygon' => '0xc2132d05d31c914a87c6611c10748aeb04b58e8f',
            'Solana' => 'Es9vMFrzaCERmJfrF4H2FYD4KCoNkY11McCe8BenwNYB',
        ],
        'USDC' => [
            'ERC20' => '0xa0b86991c6218b36c1d19d4a2e9eb0ce3606eb48',
            'Polygon' => '0x3c499c542cef5e3811e1192ce70d8cc03d5c3359',
            'Solana' => 'EPjFWdd5AufqSSqeM2qN1xzybapC8G4wEGGkZwyTDt1v',
            'Base' => '0x833589fcd6edb6e08f4c7c32d4f71b54bda02913',
            'Arbitrum' => '0xaf88d065e77c8cc2239327c5edb3a432268e5831',
        ],
    ],

    /** Network => explorer tx URL template ({hash}) */
    'explorers' => [
        'bitcoin' => 'https://mempool.space/tx/{hash}',
        'btc' => 'https://mempool.space/tx/{hash}',
        'ethereum' => 'https://etherscan.io/tx/{hash}',
        'erc20' => 'https://etherscan.io/tx/{hash}',
        'eth' => 'https://etherscan.io/tx/{hash}',
        'bep20' => 'https://bscscan.com/tx/{hash}',
        'polygon' => 'https://polygonscan.com/tx/{hash}',
        'base' => 'https://basescan.org/tx/{hash}',
        'arbitrum' => 'https://arbiscan.io/tx/{hash}',
        'tron' => 'https://tronscan.org/#/transaction/{hash}',
        'trc20' => 'https://tronscan.org/#/transaction/{hash}',
        'solana' => 'https://solscan.io/tx/{hash}',
        'sol' => 'https://solscan.io/tx/{hash}',
    ],

    /**
     * Map canonical network label → explorer client key.
     *
     * @var array<string, string>
     */
    'network_client' => [
        'Bitcoin' => 'bitcoin',
        'Ethereum' => 'ethereum',
        'ERC20' => 'ethereum',
        'TRC20' => 'tron',
        'BEP20' => 'ethereum',
        'Polygon' => 'ethereum',
        'Base' => 'ethereum',
        'Arbitrum' => 'ethereum',
        'Solana' => 'solana',
    ],

    /**
     * EVM chain metadata for Etherscan Multichain API (v2 + chainid).
     *
     * @var array<string, array{chainid: int, native: list<string>}>
     */
    'evm_chains' => [
        'Ethereum' => ['chainid' => 1, 'native' => ['ETH']],
        'ERC20' => ['chainid' => 1, 'native' => ['ETH']],
        'BEP20' => ['chainid' => 56, 'native' => ['BNB']],
        'Polygon' => ['chainid' => 137, 'native' => ['MATIC', 'POL']],
        'Base' => ['chainid' => 8453, 'native' => ['ETH']],
        'Arbitrum' => ['chainid' => 42161, 'native' => ['ETH']],
    ],

    'mempool_api' => 'https://mempool.space/api',
    'etherscan_api' => 'https://api.etherscan.io/v2/api',
    'etherscan_api_v1' => 'https://api.etherscan.io/api',
    'trongrid_api' => 'https://api.trongrid.io',
    'solana_rpc' => 'https://api.mainnet-beta.solana.com',

    'monitor_poll_seconds' => 60,
    'monitor_max_retries' => 3,
];
