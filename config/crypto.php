<?php

/**
 * CoinGecko + OTC pricing / explorer / wallet pool config for Crypto OTC v1.
 *
 * Network Registry SoT for chains: monitored_networks (+ explorers/clients).
 * Coin → networks SoT: exchange_rates.allowed_network_ids (catalog).
 * suggest_network_ids_by_coin is soft defaults only — never a runtime ceiling.
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
     * Soft defaults when creating a catalog coin (not a runtime whitelist).
     *
     * @var array<string, list<string>>
     */
    'suggest_network_ids_by_coin' => [
        'BTC' => ['bitcoin'],
        'ETH' => ['ethereum', 'base', 'arbitrum'],
        'USDT' => ['tron', 'ethereum', 'bsc', 'polygon', 'base', 'arbitrum', 'solana'],
        'USDC' => ['ethereum', 'polygon', 'solana', 'base', 'arbitrum'],
        'SOL' => ['solana'],
        'BNB' => ['bsc'],
    ],

    /**
     * @deprecated Use suggest_network_ids_by_coin. Kept for legacy readers during transition.
     * @var array<string, list<string>>
     */
    'network_ids_by_coin' => [
        'BTC' => ['bitcoin'],
        'ETH' => ['ethereum', 'base', 'arbitrum'],
        'USDT' => ['ethereum', 'tron', 'bsc', 'polygon', 'base', 'arbitrum', 'solana'],
        'USDC' => ['ethereum', 'polygon', 'solana', 'base', 'arbitrum'],
        'SOL' => ['solana'],
        'BNB' => ['bsc'],
    ],

    /**
     * @deprecated Labels are no longer stored; display via NetworkRegistry::label().
     * @var array<string, list<string>>
     */
    'networks_by_coin' => [
        'BTC' => ['Bitcoin'],
        'ETH' => ['Ethereum', 'Base', 'Arbitrum'],
        'USDT' => ['TRC20', 'ERC20', 'BEP20', 'Polygon', 'Base', 'Arbitrum', 'Solana'],
        'USDC' => ['ERC20', 'Polygon', 'Solana', 'Base', 'Arbitrum'],
        'SOL' => ['Solana'],
        'BNB' => ['BEP20'],
    ],

    'max_buy_rate_ngn_per_usd' => 10000,

    'default_coin_spread_ngn' => 25,

    'amount_precision' => [
        'BTC' => 8,
        'ETH' => 8,
        'USDT' => 6,
        'USDC' => 6,
        'SOL' => 9,
        'BNB' => 8,
    ],

    'max_active_wallets_per_network' => 5,

    'max_orders_per_wallet' => 8,

    /**
     * Default confirmations by canonical network ID (registry owns defaults).
     *
     * @var array<string, int>
     */
    'default_confirmations' => [
        'bitcoin' => 2,
        'ethereum' => 12,
        'tron' => 20,
        'bsc' => 15,
        'polygon' => 64,
        'base' => 12,
        'arbitrum' => 12,
        'solana' => 32,
    ],

    'fingerprint_max_nudges' => 50,

    'balance_dust' => [
        'BTC' => 0.00001,
        'ETH' => 0.0001,
        'USDT' => 0.01,
        'USDC' => 0.01,
        'SOL' => 0.001,
        'BNB' => 0.0001,
    ],

    /**
     * Token contracts keyed by canonical network ID.
     *
     * @var array<string, array<string, string|null>>
     */
    'token_contracts' => [
        'USDT' => [
            'tron' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
            'ethereum' => '0xdac17f958d2ee523a2206206994597c13d831ec7',
            'bsc' => '0x55d398326f99059ff775485246999027b3197955',
            'polygon' => '0xc2132d05d31c914a87c6611c10748aeb04b58e8f',
            'solana' => 'Es9vMFrzaCERmJfrF4H2FYD4KCoNkY11McCe8BenwNYB',
            'base' => '0xfde4c96c8593536e31f229ea8f37b2ada2699bb2',
            'arbitrum' => '0xfd086bc7cd5c481dcc9c85ebe478a1c0b69fcbb9',
        ],
        'USDC' => [
            'ethereum' => '0xa0b86991c6218b36c1d19d4a2e9eb0ce3606eb48',
            'polygon' => '0x3c499c542cef5e3811e1192ce70d8cc03d5c3359',
            'solana' => 'EPjFWdd5AufqSSqeM2qN1xzybapC8G4wEGGkZwyTDt1v',
            'base' => '0x833589fcd6edb6e08f4c7c32d4f71b54bda02913',
            'arbitrum' => '0xaf88d065e77c8cc2239327c5edb3a432268e5831',
        ],
    ],

    /** Network ID => explorer tx URL template ({hash}) */
    'explorers' => [
        'bitcoin' => 'https://mempool.space/tx/{hash}',
        'btc' => 'https://mempool.space/tx/{hash}',
        'ethereum' => 'https://etherscan.io/tx/{hash}',
        'erc20' => 'https://etherscan.io/tx/{hash}',
        'eth' => 'https://etherscan.io/tx/{hash}',
        'bsc' => 'https://bscscan.com/tx/{hash}',
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
     * Legacy label → network_id (aliases also live on monitored_networks).
     *
     * @var array<string, string>
     */
    'network_client' => [
        'Bitcoin' => 'bitcoin',
        'Ethereum' => 'ethereum',
        'ERC20' => 'ethereum',
        'TRC20' => 'tron',
        'TRON' => 'tron',
        'BEP20' => 'bsc',
        'BSC' => 'bsc',
        'Polygon' => 'polygon',
        'Base' => 'base',
        'Arbitrum' => 'arbitrum',
        'Arbitrum One' => 'arbitrum',
        'Solana' => 'solana',
    ],

    /**
     * Network Registry: available blockchains + explorer clients.
     *
     * @var array<string, array{
     *   label: string,
     *   aliases: list<string>,
     *   native_client: string,
     *   chainid?: int,
     *   ui_provider_native?: string,
     *   blockchain_com?: bool
     * }>
     */
    'monitored_networks' => [
        'bitcoin' => [
            'label' => 'Bitcoin',
            'aliases' => ['Bitcoin', 'BTC'],
            'native_client' => 'mempool',
            'ui_provider_native' => 'Public explorer',
            'blockchain_com' => true,
        ],
        'ethereum' => [
            'label' => 'Ethereum (ERC20)',
            'aliases' => ['Ethereum', 'ERC20', 'ETH'],
            'native_client' => 'etherscan',
            'chainid' => 1,
            'ui_provider_native' => 'Native',
            'blockchain_com' => true,
        ],
        'bsc' => [
            'label' => 'BNB Smart Chain (BEP20)',
            'aliases' => ['BEP20', 'BSC', 'bep20', 'BNB Smart Chain'],
            'native_client' => 'etherscan',
            'chainid' => 56,
            'ui_provider_native' => 'Native',
            'blockchain_com' => false,
        ],
        'polygon' => [
            'label' => 'Polygon',
            'aliases' => ['Polygon', 'MATIC'],
            'native_client' => 'etherscan',
            'chainid' => 137,
            'ui_provider_native' => 'Native',
            'blockchain_com' => false,
        ],
        'base' => [
            'label' => 'Base',
            'aliases' => ['Base'],
            'native_client' => 'etherscan',
            'chainid' => 8453,
            'ui_provider_native' => 'Native',
            'blockchain_com' => false,
        ],
        'arbitrum' => [
            'label' => 'Arbitrum One',
            'aliases' => ['Arbitrum', 'Arbitrum One', 'ARB'],
            'native_client' => 'etherscan',
            'chainid' => 42161,
            'ui_provider_native' => 'Native',
            'blockchain_com' => false,
        ],
        'tron' => [
            'label' => 'TRON (TRC20)',
            'aliases' => ['TRC20', 'TRON'],
            'native_client' => 'trongrid',
            'ui_provider_native' => 'Native',
            'blockchain_com' => false,
        ],
        'solana' => [
            'label' => 'Solana',
            'aliases' => ['Solana', 'SOL'],
            'native_client' => 'solana',
            'ui_provider_native' => 'Native',
            'blockchain_com' => true,
        ],
    ],

    'monitor_providers' => [
        'native' => [
            'label' => 'Native',
            'enabled' => true,
            'description' => 'Public Bitcoin explorer, EVM explorer API, TRON API, Solana RPC.',
        ],
        'blockchain_com' => [
            'label' => 'Blockchain.com',
            'enabled' => true,
            'description' => 'Explorer Gateway for Bitcoin, Ethereum, and Solana. TRON still uses Native TRON API.',
        ],
        'blockchair' => [
            'label' => 'Blockchair',
            'enabled' => false,
            'description' => 'Coming soon.',
        ],
        'blockcypher' => [
            'label' => 'BlockCypher',
            'enabled' => false,
            'description' => 'Coming soon.',
        ],
        'custom' => [
            'label' => 'Custom',
            'enabled' => false,
            'description' => 'Coming soon.',
        ],
    ],

    'monitor_credential_keys' => [
        'etherscan_api_key',
        'trongrid_api_key',
        'solana_rpc_url',
        'blockchain_com_api_key',
        'blockchain_com_base_url',
    ],

    /**
     * EVM chain metadata keyed by canonical network ID.
     *
     * @var array<string, array{chainid: int, native: list<string>}>
     */
    'evm_chains' => [
        'ethereum' => ['chainid' => 1, 'native' => ['ETH']],
        'bsc' => ['chainid' => 56, 'native' => ['BNB']],
        'polygon' => ['chainid' => 137, 'native' => ['MATIC', 'POL']],
        'base' => ['chainid' => 8453, 'native' => ['ETH']],
        'arbitrum' => ['chainid' => 42161, 'native' => ['ETH']],
        // Legacy label keys (transition).
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

    'blockchain_com' => [
        'base_url' => env('BLOCKCHAIN_COM_EXPLORER_BASE', 'https://api.blockchain.info/explorer-gateway-kt'),
        'auth_header' => 'X-Explorer-Auth-Key',
        'supported_networks' => ['bitcoin', 'ethereum', 'solana'],
    ],

    'monitor_poll_seconds' => 60,
    'monitor_max_retries' => 3,
];
