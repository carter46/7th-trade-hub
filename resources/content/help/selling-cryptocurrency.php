<?php

return [
    'slug' => 'selling-cryptocurrency',
    'category_key' => 'crypto-exchange',
    'title' => 'Selling Cryptocurrency on 7th Trade Hub',
    'intro' => 'Estimate payouts, submit a sell request, and understand how admin approval credits your Naira wallet.',
    'summary' => 'Crypto sells use live sell rates, a calculator for estimates, and admin review before NGN credit.',
    'updated_at' => '2026-07-20',
    'printable' => true,
    'related' => ['billing-wallets-payments', 'getting-started'],
    'platform_actions' => [
        ['label' => 'Exchange rates', 'route' => 'exchange'],
        ['label' => 'Wallet', 'route' => 'dashboard.wallet', 'auth' => true],
        ['label' => 'Contact support', 'route' => 'contact'],
    ],
    'sections' => [
        [
            'id' => 'supported',
            'nav' => 'Supported cryptocurrencies',
            'title' => 'Supported cryptocurrencies',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'The Exchange page lists assets the platform currently accepts for sell requests. Availability can change — always check the live list before sending funds.'],
            ],
        ],
        [
            'id' => 'rates',
            'nav' => 'Sell rates',
            'title' => 'Sell rates',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Sell rates are the Naira amounts the platform offers per unit of crypto. Rates update periodically; the rate locked on your approved request is what credits your wallet.'],
                ['type' => 'important', 'title' => 'Estimates vs final credit', 'content' => 'Calculator results are estimates. Final NGN credit follows admin confirmation of the received amount and applicable rate.'],
            ],
        ],
        [
            'id' => 'calculator',
            'nav' => 'Exchange calculator',
            'title' => 'Exchange calculator',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Use the public Exchange calculator to estimate how much NGN you may receive for a given crypto amount.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Exchange page',
                    'caption' => 'Open Exchange from the main navigation to view rates and the calculator.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Exchange page screenshot',
                ],
                [
                    'type' => 'screenshot',
                    'title' => 'Calculator',
                    'caption' => 'Enter an amount to preview an estimated Naira payout.',
                    'size' => 'medium',
                    'alignment' => 'center',
                    'alt' => 'Exchange calculator screenshot',
                ],
            ],
        ],
        [
            'id' => 'sell-request',
            'nav' => 'Creating a sell request',
            'title' => 'Creating a sell request',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'From your dashboard crypto sell flow, choose the asset, amount, and follow on-screen instructions. You will receive deposit details for the sell.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Sell form',
                    'caption' => 'Dashboard crypto sell form used to create a request.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Crypto sell form screenshot',
                ],
            ],
        ],
        [
            'id' => 'wallet-addresses',
            'nav' => 'Wallet addresses',
            'title' => 'Wallet addresses',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Send crypto only to the address shown for your active sell request and network. Sending to the wrong network or a reused expired quote can delay or lose funds.'],
                ['type' => 'warning', 'title' => 'Double-check the network', 'content' => 'Confirm asset and network (for example USDT on the network specified) before broadcasting the transaction.'],
            ],
        ],
        [
            'id' => 'approval',
            'nav' => 'Approval process',
            'title' => 'Approval process',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'After you send crypto, admins verify receipt and approve or reject the request. Status updates appear in your sell history.'],
            ],
        ],
        [
            'id' => 'credit',
            'nav' => 'Wallet credit after approval',
            'title' => 'Wallet credit after approval',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'When approved, NGN is credited to your platform wallet. You can then withdraw, buy services, or pay for marketplace escrow checkout.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Completed exchange',
                    'caption' => 'Approved sell reflected as a completed wallet credit.',
                    'size' => 'medium',
                    'alignment' => 'center',
                    'alt' => 'Completed crypto exchange screenshot',
                ],
            ],
        ],
        [
            'id' => 'history',
            'nav' => 'Exchange history',
            'title' => 'Exchange history',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Track pending and completed sells from your dashboard history and crypto sell list.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Transaction history',
                    'caption' => 'Dashboard → History for deposits, sells, and related ledger entries.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Transaction history screenshot',
                ],
            ],
        ],
        [
            'id' => 'delays',
            'nav' => 'Common reasons for delays',
            'title' => 'Common reasons for delays',
            'blocks' => [
                ['type' => 'bullets', 'items' => [
                    'Blockchain confirmation time or network congestion',
                    'Wrong network or incomplete transfer amount',
                    'Expired quote — create a new sell request',
                    'Peak review volume on the admin side',
                ]],
            ],
        ],
        [
            'id' => 'faqs',
            'nav' => 'FAQs',
            'title' => 'Frequently asked questions',
            'blocks' => [
                [
                    'type' => 'faq',
                    'items' => [
                        ['q' => 'Is the calculator rate guaranteed?', 'a' => 'No. It is an estimate. Final credit uses the confirmed receipt and rate at approval.'],
                        ['q' => 'Can I cancel a sell after sending crypto?', 'a' => 'Contact support immediately. Once funds are received and processing, cancellation may not be possible.'],
                        ['q' => 'Where does NGN appear?', 'a' => 'In your Naira wallet balance after admin approval.'],
                    ],
                ],
            ],
        ],
    ],
];
