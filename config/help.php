<?php

return [
    'articles_path' => resource_path('content/help'),

    /*
    |--------------------------------------------------------------------------
    | Help Center categories (hub cards)
    |--------------------------------------------------------------------------
    */
    'categories' => [
        [
            'key' => 'getting-started',
            'article' => 'getting-started',
            'title' => 'Getting Started',
            'description' => 'Account setup, email verification, wallet creation, and your first steps on 7th Trade Hub.',
            'icon' => 'rocket',
            'cta' => 'Read guide',
            'tone' => 'primary',
        ],
        [
            'key' => 'crypto-exchange',
            'article' => 'selling-cryptocurrency',
            'title' => 'Crypto Exchange',
            'description' => 'Sell rates, calculator estimates, wallet credit after approval, and crypto-to-Naira sell requests.',
            'icon' => 'swap',
            'cta' => 'Read guide',
            'tone' => 'accent',
        ],
        [
            'key' => 'services',
            'article' => 'browsing-purchasing-services',
            'title' => 'Service Management',
            'description' => 'Browse and buy platform services — network, social, websites, documents, and more.',
            'icon' => 'grid',
            'cta' => 'Read guide',
            'tone' => 'warning',
        ],
        [
            'key' => 'billing',
            'article' => 'billing-wallets-payments',
            'title' => 'Billing & Payments',
            'description' => 'Fund your Naira wallet, bank deposits, crypto sells, checkout, and withdrawal basics.',
            'icon' => 'wallet',
            'cta' => 'Read guide',
            'tone' => 'success',
        ],
        [
            'key' => 'security',
            'article' => 'keeping-account-secure',
            'title' => 'Security & Trust',
            'description' => 'KYC verification, escrow-protected marketplace purchases, and keeping your account secure.',
            'icon' => 'lock',
            'cta' => 'Read guide',
            'tone' => 'info',
        ],
        [
            'key' => 'marketplace',
            'article' => 'buying-selling-marketplace',
            'title' => 'Marketplace',
            'description' => 'Buy and sell listings with escrow, reviews, watchlists, and order delivery confirmation.',
            'icon' => 'storefront',
            'cta' => 'Read guide',
            'tone' => 'primary',
        ],
    ],

    'faqs' => [
        [
            'q' => 'How do I fund my wallet?',
            'a' => 'Open your dashboard Wallet, then use Bank deposit or Crypto sell. Bank deposits are reviewed by admin; crypto sells use the live sell rate and credit NGN after approval.',
            'article' => 'billing-wallets-payments',
            'section' => 'funding',
        ],
        [
            'q' => 'How does crypto-to-cash exchange work?',
            'a' => 'Use the public Exchange page to estimate your payout, then start a sell request from your dashboard. Final credit uses the confirmed rate and admin approval.',
            'article' => 'selling-cryptocurrency',
            'section' => 'sell-request',
        ],
        [
            'q' => 'How do I buy a service?',
            'a' => 'Browse Services, open a product, choose a plan if available, then Buy Now. You will be asked to log in if needed, then continue to platform checkout with your wallet.',
            'article' => 'browsing-purchasing-services',
            'section' => 'checkout',
        ],
        [
            'q' => 'What is marketplace escrow?',
            'a' => 'When you buy an eligible listing, funds are held in escrow until you confirm delivery. That protects both buyer and seller during the trade.',
            'article' => 'buying-selling-marketplace',
            'section' => 'escrow-checkout',
        ],
    ],
];
