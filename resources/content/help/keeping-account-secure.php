<?php

return [
    'slug' => 'keeping-account-secure',
    'category_key' => 'security',
    'title' => 'Keeping Your Account Secure',
    'intro' => 'Verify your email, strengthen passwords, complete KYC when needed, and use escrow-aware buying practices.',
    'summary' => 'Security on 7th Trade Hub combines account hygiene, KYC, escrow protection, and scam awareness.',
    'updated_at' => '2026-07-20',
    'hero_image' => 'assets/images/programming-background-with-person-working-with-codes-computer copy.jpg',
    'printable' => true,
    'related' => ['getting-started', 'buying-selling-marketplace', 'billing-wallets-payments'],
    'platform_actions' => [
        ['label' => 'KYC', 'route' => 'dashboard.kyc', 'auth' => true],
        ['label' => 'Contact us', 'route' => 'contact'],
        ['label' => 'Help Center', 'route' => 'help'],
    ],
    'sections' => [
        [
            'id' => 'email-verification',
            'nav' => 'Email verification',
            'title' => 'Email verification',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Verify your email after registration so you can recover access and receive security notices. Unverified emails may limit sensitive actions.'],
            ],
        ],
        [
            'id' => 'passwords',
            'nav' => 'Password security',
            'title' => 'Password security',
            'blocks' => [
                ['type' => 'checklist', 'items' => [
                    'Use a long, unique password',
                    'Never share passwords or OTP codes',
                    'Change your password if you suspect compromise',
                ]],
                [
                    'type' => 'screenshot',
                    'title' => 'Security settings',
                    'caption' => 'Profile / password area in your account settings.',
                    'size' => 'medium',
                    'alignment' => 'center',
                    'alt' => 'Security settings screenshot',
                ],
            ],
        ],
        [
            'id' => 'kyc',
            'nav' => 'KYC verification',
            'title' => 'KYC verification',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Submit government ID and required documents from the KYC page when prompted. Approval unlocks higher trust limits for wallet activity.'],
                [
                    'type' => 'screenshot',
                    'title' => 'KYC page',
                    'caption' => 'Dashboard → KYC for document upload and status.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'KYC page screenshot',
                ],
                [
                    'type' => 'screenshot',
                    'title' => 'Verification status',
                    'caption' => 'Pending, approved, or rejected status after review.',
                    'size' => 'medium',
                    'alignment' => 'center',
                    'alt' => 'KYC verification status screenshot',
                ],
            ],
        ],
        [
            'id' => 'secure-purchases',
            'nav' => 'Secure marketplace purchases',
            'title' => 'Secure marketplace purchases',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Prefer in-platform checkout with escrow. Avoid off-platform payment requests from sellers or buyers.'],
            ],
        ],
        [
            'id' => 'escrow',
            'nav' => 'Escrow protection',
            'title' => 'Escrow protection',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Eligible marketplace orders hold buyer funds until delivery is confirmed, reducing risk of non-delivery or unpaid work.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Escrow checkout',
                    'caption' => 'Checkout screen showing escrow-protected payment.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Escrow checkout screenshot',
                ],
            ],
        ],
        [
            'id' => 'scams',
            'nav' => 'Recognizing scams',
            'title' => 'Recognizing scams',
            'blocks' => [
                ['type' => 'warning', 'title' => 'Red flags', 'content' => 'Anyone asking you to pay outside the platform, share OTPs, or “upgrade” by sending crypto to a personal wallet is likely a scam.'],
                ['type' => 'bullets', 'items' => [
                    'Too-good-to-be-true prices with pressure to act fast',
                    'Requests to move the chat to WhatsApp for payment',
                    'Fake support accounts asking for your password',
                ]],
            ],
        ],
        [
            'id' => 'safe-buying',
            'nav' => 'Safe buying practices',
            'title' => 'Safe buying practices',
            'blocks' => [
                ['type' => 'checklist', 'items' => [
                    'Read listing details and seller reputation',
                    'Pay only through platform checkout',
                    'Confirm delivery before releasing escrow',
                    'Leave an honest review after completion',
                ]],
            ],
        ],
        [
            'id' => 'reporting',
            'nav' => 'Reporting suspicious activity',
            'title' => 'Reporting suspicious activity',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Open a support ticket or use Contact Us immediately if you spot fraud, phishing, or account takeover attempts.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Support page',
                    'caption' => 'Dashboard support tickets or Contact Us for live help.',
                    'size' => 'medium',
                    'alignment' => 'center',
                    'alt' => 'Support page screenshot',
                ],
            ],
        ],
        [
            'id' => 'best-practices',
            'nav' => 'Best practices',
            'title' => 'Security best practices',
            'blocks' => [
                ['type' => 'success', 'title' => 'Stay protected', 'content' => 'Verify email, use a strong unique password, complete KYC when required, keep delivery credentials private, and never bypass escrow.'],
            ],
        ],
    ],
];
