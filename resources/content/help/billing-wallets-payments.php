<?php

return [
    'slug' => 'billing-wallets-payments',
    'category_key' => 'billing',
    'title' => 'Billing, Wallets and Payments',
    'intro' => 'Fund your Naira wallet, understand deposits and withdrawals, and follow payment statuses through checkout and history.',
    'summary' => 'Wallet top-ups use Monnify checkout or reserved accounts (plus crypto sells). Platform service orders can also be paid by bank transfer at checkout when enabled.',
    'updated_at' => '2026-07-20',
    'hero_image' => 'assets/images/crytpo_exchange.jpg',
    'printable' => true,
    'related' => ['selling-cryptocurrency', 'getting-started', 'buying-selling-marketplace'],
    'platform_actions' => [
        ['label' => 'Wallet', 'route' => 'dashboard.wallet', 'auth' => true],
        ['label' => 'Exchange', 'route' => 'exchange'],
        ['label' => 'Support', 'route' => 'contact'],
    ],
    'sections' => [
        [
            'id' => 'funding',
            'nav' => 'Funding the Naira wallet',
            'title' => 'Funding the Naira wallet',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Fund your wallet via Monnify checkout or your reserved account (when available). Crypto sells also credit NGN after admin review. Manual bank transfer is not used for wallet top-ups — it is only for paying platform service orders at checkout when the admin enables it.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Wallet page',
                    'caption' => 'Dashboard → Wallet for balances and actions.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Wallet page screenshot',
                ],
            ],
        ],
        [
            'id' => 'bank-deposits',
            'nav' => 'Wallet funding',
            'title' => 'Wallet funding',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Use Dashboard → Wallet → Fund wallet for Monnify card/transfer checkout or your dedicated reserved account. Crypto sells are a separate OTC flow. Bank transfer at checkout is for platform service orders only, not wallet deposits.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Deposit page',
                    'caption' => 'Bank deposit instructions and submission form.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Deposit page screenshot',
                ],
                ['type' => 'important', 'title' => 'Use the exact reference', 'content' => 'Follow on-screen payment details so admins can match your transfer quickly.'],
            ],
        ],
        [
            'id' => 'crypto-sells',
            'nav' => 'Crypto sells',
            'title' => 'Crypto sells',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Crypto sells convert supported assets to NGN after admin approval. See the Selling Cryptocurrency guide for rates, addresses, and delays.'],
                ['type' => 'tip', 'title' => 'Related guide', 'content' => 'Read Selling Cryptocurrency on 7th Trade Hub for the full exchange flow.'],
            ],
        ],
        [
            'id' => 'balances',
            'nav' => 'Wallet balances',
            'title' => 'Wallet balances',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Your available balance can be used for service checkout, marketplace escrow, and eligible withdrawals. Pending deposits do not spend until approved.'],
            ],
        ],
        [
            'id' => 'checkout-payments',
            'nav' => 'Checkout payments',
            'title' => 'Checkout payments',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'At checkout you can pay from wallet balance, via the payment gateway, or by bank transfer to the company account (when enabled). Marketplace escrow still uses wallet balance.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Checkout',
                    'caption' => 'Wallet-funded checkout confirmation.',
                    'size' => 'medium',
                    'alignment' => 'center',
                    'alt' => 'Checkout payment screenshot',
                ],
            ],
        ],
        [
            'id' => 'withdrawals',
            'nav' => 'Withdrawals',
            'title' => 'Withdrawals',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Request a withdrawal from the Wallet area. Provide valid bank details and amount within platform min/max limits. Admin review applies before payout.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Withdrawal page',
                    'caption' => 'Submit and track withdrawal requests.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Withdrawal page screenshot',
                ],
                ['type' => 'warning', 'title' => 'KYC may be required', 'content' => 'Higher limits or withdrawals can require completed KYC verification.'],
            ],
        ],
        [
            'id' => 'history',
            'nav' => 'Transaction history',
            'title' => 'Transaction history',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'History lists deposits, sells, purchases, escrow moves, and withdrawals with timestamps and statuses.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Payment history',
                    'caption' => 'Dashboard history of wallet movements.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Payment history screenshot',
                ],
                [
                    'type' => 'screenshot',
                    'title' => 'Transaction details',
                    'caption' => 'Open a row for reference IDs and status notes.',
                    'size' => 'medium',
                    'alignment' => 'center',
                    'alt' => 'Transaction details screenshot',
                ],
            ],
        ],
        [
            'id' => 'statuses',
            'nav' => 'Payment statuses',
            'title' => 'Understanding payment statuses',
            'blocks' => [
                ['type' => 'bullets', 'items' => [
                    'Pending — awaiting payment proof, blockchain confirmations, or admin review',
                    'Approved / Completed — funds credited or order paid successfully',
                    'Rejected / Failed — not credited; check notes or contact support',
                    'Escrow held — marketplace funds locked until delivery confirmation',
                ]],
            ],
        ],
        [
            'id' => 'faqs',
            'nav' => 'Common questions',
            'title' => 'Common payment questions',
            'blocks' => [
                [
                    'type' => 'faq',
                    'items' => [
                        ['q' => 'How long do bank deposits take?', 'a' => 'Usually after admin confirms your transfer — timing depends on banking and review queue.'],
                        ['q' => 'Why was my deposit rejected?', 'a' => 'Mismatched amount, missing proof, or incorrect reference are common causes. Resubmit with correct details or contact support.'],
                        ['q' => 'Can I pay checkout with card directly?', 'a' => 'Checkout uses your platform wallet balance. Fund the wallet first, then pay.'],
                    ],
                ],
            ],
        ],
    ],
];
