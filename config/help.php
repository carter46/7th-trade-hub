<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Help Center categories
    |--------------------------------------------------------------------------
    | Linked to real platform areas where possible. Icons map to resources/icons.
    */
    'categories' => [
        [
            'key' => 'getting-started',
            'title' => 'Getting Started',
            'description' => 'Account setup, email verification, wallet creation, and your first steps on 7th Trade Hub.',
            'icon' => 'rocket',
            'cta' => 'Explore guides',
            'href' => 'register',
            'tone' => 'primary',
        ],
        [
            'key' => 'crypto-exchange',
            'title' => 'Crypto Exchange',
            'description' => 'Sell rates, calculator estimates, wallet credit after approval, and crypto-to-Naira sell requests.',
            'icon' => 'swap',
            'cta' => 'Exchange support',
            'href' => 'exchange',
            'tone' => 'accent',
        ],
        [
            'key' => 'services',
            'title' => 'Service Management',
            'description' => 'Browse and buy platform services — network, social, websites, documents, and more.',
            'icon' => 'grid',
            'cta' => 'Browse services',
            'href' => 'services',
            'tone' => 'warning',
        ],
        [
            'key' => 'billing',
            'title' => 'Billing & Payments',
            'description' => 'Fund your Naira wallet, bank deposits, crypto sells, checkout, and withdrawal basics.',
            'icon' => 'wallet',
            'cta' => 'Wallet help',
            'href' => 'dashboard.wallet',
            'auth' => true,
            'guest_href' => 'login',
            'tone' => 'success',
        ],
        [
            'key' => 'security',
            'title' => 'Security & Trust',
            'description' => 'KYC verification, escrow-protected marketplace purchases, and keeping your account secure.',
            'icon' => 'lock',
            'cta' => 'Security guides',
            'href' => 'dashboard.kyc',
            'auth' => true,
            'guest_href' => 'login',
            'tone' => 'info',
        ],
        [
            'key' => 'marketplace',
            'title' => 'Marketplace',
            'description' => 'Buy and sell listings with escrow, reviews, watchlists, and order delivery confirmation.',
            'icon' => 'storefront',
            'cta' => 'Open marketplace',
            'href' => 'marketplace',
            'tone' => 'primary',
        ],
    ],

    'faqs' => [
        [
            'q' => 'How do I fund my wallet?',
            'a' => 'Open your dashboard Wallet, then use Bank deposit or Crypto sell. Bank deposits are reviewed by admin; crypto sells use the live sell rate and credit NGN after approval.',
        ],
        [
            'q' => 'How does crypto-to-cash exchange work?',
            'a' => 'Use the public Exchange page to estimate your payout, then start a sell request from your dashboard. Final credit uses the confirmed rate and admin approval.',
        ],
        [
            'q' => 'How do I buy a service?',
            'a' => 'Browse Services, open a product, choose a plan if available, then Buy Now. You will be asked to log in if needed, then continue to platform checkout with your wallet.',
        ],
        [
            'q' => 'What is marketplace escrow?',
            'a' => 'When you buy an eligible listing, funds are held in escrow until you confirm delivery. That protects both buyer and seller during the trade.',
        ],
    ],
];
