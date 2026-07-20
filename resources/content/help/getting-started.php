<?php

return [
    'slug' => 'getting-started',
    'category_key' => 'getting-started',
    'title' => 'Getting Started with 7th Trade Hub',
    'intro' => 'Create your account, verify your email, set up your profile and wallet, and learn how to navigate the dashboard.',
    'summary' => 'This guide walks you from registration through your first transaction and where to find support.',
    'updated_at' => '2026-07-20',
    'printable' => true,
    'related' => ['billing-wallets-payments', 'buying-selling-marketplace', 'keeping-account-secure'],
    'platform_actions' => [
        ['label' => 'Create account', 'route' => 'register'],
        ['label' => 'Open dashboard', 'route' => 'dashboard', 'auth' => true],
        ['label' => 'Help Center', 'route' => 'help'],
    ],
    'sections' => [
        [
            'id' => 'create-account',
            'nav' => 'Creating an account',
            'title' => 'Creating an account',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Visit the registration page and enter your name, email, username, and a strong password. Agree to the Terms of Service and Privacy Policy before submitting.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Registration page',
                    'caption' => 'Navigate to Register from the top navigation or marketing footer.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Registration page screenshot',
                ],
                ['type' => 'tip', 'title' => 'Quick tip', 'content' => 'Use an email you can access immediately — you will need it for verification before using many wallet features.'],
            ],
        ],
        [
            'id' => 'email-verification',
            'nav' => 'Email verification',
            'title' => 'Email verification',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'After registering, check your inbox for a one-time verification code (OTP). Enter the code on the verification screen to activate your account email.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Email verification',
                    'caption' => 'Enter the OTP sent to your email to continue.',
                    'size' => 'medium',
                    'alignment' => 'center',
                    'alt' => 'Email verification screen',
                ],
                ['type' => 'important', 'title' => 'Didn’t get the code?', 'content' => 'Check spam/junk, wait a minute, then use Resend. Make sure the email address was typed correctly during registration.'],
            ],
        ],
        [
            'id' => 'logging-in',
            'nav' => 'Logging in',
            'title' => 'Logging in',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Use your email (or username, if enabled) and password on the Login page. After login you are taken to your user dashboard.'],
            ],
        ],
        [
            'id' => 'profile',
            'nav' => 'Setting up your profile',
            'title' => 'Setting up your profile',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Open your profile settings to add contact details and keep your account information accurate. Complete KYC later when you need higher wallet limits or withdrawals.'],
                ['type' => 'checklist', 'items' => [
                    'Confirm your display name and contact details',
                    'Use a unique password you do not reuse elsewhere',
                    'Review notification preferences if available',
                ]],
            ],
        ],
        [
            'id' => 'wallet',
            'nav' => 'Wallet creation',
            'title' => 'Wallet creation',
            'blocks' => [
                ['type' => 'paragraph', 'content' => '7th Trade Hub uses a Naira (NGN) wallet for deposits, marketplace escrow, service checkout, and withdrawals. Your wallet is provisioned with your account so you can fund it from the Wallet area.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Wallet page',
                    'caption' => 'Dashboard → Wallet shows balances and funding options.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Wallet dashboard screenshot',
                ],
            ],
        ],
        [
            'id' => 'dashboard',
            'nav' => 'Dashboard overview',
            'title' => 'Dashboard overview',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Your dashboard summarizes wallet balance, recent activity, orders, and shortcuts to listings, support, and KYC.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Dashboard',
                    'caption' => 'Signed-in home for wallet, orders, and account tools.',
                    'size' => 'large',
                    'alignment' => 'full',
                    'alt' => 'User dashboard overview',
                ],
            ],
        ],
        [
            'id' => 'navigation',
            'nav' => 'Understanding navigation',
            'title' => 'Understanding the navigation',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Marketing pages use the top nav: Home, Services, Marketplace, Exchange, and Help. Inside the dashboard, the sidebar covers Wallet, Orders, Listings, Messages, Support, and Settings.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Navigation menu',
                    'caption' => 'Use the marketing nav for public pages and the dashboard sidebar for account tools.',
                    'size' => 'medium',
                    'alignment' => 'center',
                    'alt' => 'Navigation menu screenshot',
                ],
            ],
        ],
        [
            'id' => 'first-transaction',
            'nav' => 'Your first transaction',
            'title' => 'Your first transaction',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Fund your wallet with a bank deposit or crypto sell, then buy a service or marketplace listing. Always confirm amounts and statuses in Transaction history.'],
                ['type' => 'success', 'title' => 'Good first steps', 'content' => 'Start with a small bank deposit or a small service purchase so you can learn statuses (pending, approved, completed) safely.'],
            ],
        ],
        [
            'id' => 'support',
            'nav' => 'Where to find support',
            'title' => 'Where to find support',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Use this Help Center for guides, open support tickets from your dashboard, or visit Contact Us for live chat, phone, and email.'],
                ['type' => 'bullets', 'items' => [
                    'Help Center — searchable guides and FAQs',
                    'My tickets — track conversations with support',
                    'Contact Us — live chat and direct contact details',
                ]],
            ],
        ],
    ],
];
