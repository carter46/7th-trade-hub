<?php

return [
    'updated_at' => '2026-07-01',

    'contact' => [
        'email' => env('LEGAL_CONTACT_EMAIL'),
    ],

    'documents' => [
        'terms' => [
            'label' => 'Terms of Service',
            'eyebrow' => 'Compliance & Legal',
            'intro' => 'Please review the rules for using 7th Trade Hub — wallet, marketplace, services, and crypto exchange.',
            'summary' => 'By using 7th Trade Hub you agree to our platform rules, KYC requirements where applicable, escrow marketplace policies, and wallet / exchange terms. This document was last updated July 2026.',
            'sections' => [
                [
                    'id' => 'acceptance',
                    'nav' => '1. Acceptance of Terms',
                    'title' => 'Acceptance of Terms',
                    'number' => '01',
                    'paragraphs' => [
                        'By accessing or using 7th Trade Hub (“the Platform”), including the marketplace, digital services catalog, Naira wallet, and crypto-to-cash exchange, you confirm that you have read, understood, and agree to these Terms of Service.',
                        'If you do not agree, you must stop using the Platform. We may update these terms from time to time; continued use after changes are posted means you accept the updated terms.',
                    ],
                ],
                [
                    'id' => 'security',
                    'nav' => '2. Accounts & Security',
                    'title' => 'User Accounts & Security',
                    'number' => '02',
                    'paragraphs' => [
                        'Account security is a shared responsibility. You agree to:',
                    ],
                    'checklist' => [
                        'Provide accurate information during registration and any KYC (Know Your Customer) process.',
                        'Keep your login credentials confidential and protect access to your devices.',
                        'Notify support promptly if you suspect unauthorized access to your account.',
                    ],
                ],
                [
                    'id' => 'marketplace',
                    'nav' => '3. Marketplace & Services',
                    'title' => 'Marketplace & Digital Services',
                    'number' => '03',
                    'paragraphs' => [
                        'The Platform lets users list and purchase digital goods and services, and browse platform-operated service products. Eligible marketplace purchases may use escrow until delivery is confirmed.',
                    ],
                    'cards' => [
                        [
                            'title' => 'Marketplace escrow',
                            'body' => 'Funds for eligible orders are held until the buyer confirms delivery. Misuse of escrow or false delivery claims may lead to account action.',
                        ],
                        [
                            'title' => 'Platform services',
                            'body' => 'Catalog products (network, social, websites, documents, and related plans) are fulfilled according to the product description and checkout terms shown at purchase.',
                        ],
                    ],
                ],
                [
                    'id' => 'financial',
                    'nav' => '4. Financial Transactions',
                    'title' => 'Financial Transactions',
                    'number' => '04',
                    'paragraphs' => [
                        'Wallet funding, withdrawals, marketplace checkout, and crypto sell requests are subject to verification, admin review where required, and applicable fees or network costs.',
                    ],
                    'bullets' => [
                        'Sell rates shown on the Exchange page are estimates; the confirmed rate applies when your sell request is processed.',
                        'You are responsible for providing correct bank or crypto destination details. We are not liable for losses from incorrect details you supply.',
                        'Deposits, withdrawals, and crypto sells may take from minutes up to longer review windows depending on method and compliance checks.',
                    ],
                ],
                [
                    'id' => 'prohibited',
                    'nav' => '5. Prohibited Activities',
                    'title' => 'Prohibited Activities',
                    'number' => '05',
                    'variant' => 'danger',
                    'paragraphs' => [
                        'You must not use the Platform for:',
                    ],
                    'blocks' => [
                        'Fraud, phishing, or fake listings',
                        'Money laundering or illegal payments',
                        'Abuse of escrow or chargeback fraud',
                        'Scraping, attacks, or account takeover',
                    ],
                ],
                [
                    'id' => 'ip',
                    'nav' => '6. Intellectual Property',
                    'title' => 'Intellectual Property',
                    'number' => '06',
                    'paragraphs' => [
                        'Platform software, branding, UI, and content remain the property of 7th Trade Hub and its licensors. You may not copy or redistribute them without permission, except as allowed by purchased product licenses.',
                    ],
                ],
                [
                    'id' => 'liability',
                    'nav' => '7. Limitation of Liability',
                    'title' => 'Limitation of Liability',
                    'number' => '07',
                    'paragraphs' => [
                        'To the fullest extent permitted by law, 7th Trade Hub is not liable for indirect, incidental, or consequential damages arising from use of the Platform, including delays in funding, exchange processing, or third-party network issues.',
                    ],
                ],
                [
                    'id' => 'contact',
                    'nav' => '8. Contact',
                    'title' => 'Contact Information',
                    'number' => '08',
                    'variant' => 'contact',
                    'paragraphs' => [
                        'Questions about these Terms can be sent through a support ticket on the Platform.',
                    ],
                ],
            ],
        ],

        'privacy' => [
            'label' => 'Privacy Policy',
            'eyebrow' => 'Compliance & Legal',
            'intro' => 'How 7th Trade Hub collects, uses, and protects personal data across wallet, KYC, marketplace, and support workflows.',
            'summary' => 'We collect account, transaction, and KYC data needed to run the Platform, prevent fraud, and meet legal obligations. We do not sell your personal data. This policy was last updated July 2026.',
            'sections' => [
                [
                    'id' => 'collect',
                    'nav' => '1. What We Collect',
                    'title' => 'What We Collect',
                    'number' => '01',
                    'paragraphs' => [
                        'Depending on how you use the Platform, we may collect:',
                    ],
                    'bullets' => [
                        'Account details such as name, email, username, and contact information.',
                        'KYC documents and verification status when required for wallet or compliance features.',
                        'Transaction records (deposits, withdrawals, orders, crypto sells, and support tickets).',
                        'Technical logs such as IP address, device/browser data, and security events.',
                    ],
                ],
                [
                    'id' => 'use',
                    'nav' => '2. How We Use Data',
                    'title' => 'How We Use Data',
                    'number' => '02',
                    'paragraphs' => [
                        'We use personal data to operate and secure the Platform, process payments and escrow, complete KYC, respond to support requests, improve services, and comply with applicable law.',
                    ],
                ],
                [
                    'id' => 'sharing',
                    'nav' => '3. Sharing',
                    'title' => 'Sharing',
                    'number' => '03',
                    'paragraphs' => [
                        'We share data only as needed with infrastructure and service providers (for example hosting, email, and payment processors), with counterparties as required to complete a trade you initiate, or when required by law or to protect the Platform and users.',
                    ],
                ],
                [
                    'id' => 'cookies',
                    'nav' => '4. Cookies & Sessions',
                    'title' => 'Cookies & Sessions',
                    'number' => '04',
                    'paragraphs' => [
                        'We use essential cookies and session storage to keep you signed in, protect against CSRF, and maintain security. Disabling essential cookies may prevent the Platform from working correctly.',
                    ],
                ],
                [
                    'id' => 'retention',
                    'nav' => '5. Retention & Security',
                    'title' => 'Retention & Security',
                    'number' => '05',
                    'paragraphs' => [
                        'We retain data as long as needed for the purposes above, including legal and accounting retention. We apply technical and organizational measures appropriate to the risk, but no method of transmission or storage is perfectly secure.',
                    ],
                ],
                [
                    'id' => 'rights',
                    'nav' => '6. Your Choices',
                    'title' => 'Your Choices',
                    'number' => '06',
                    'paragraphs' => [
                        'You may update profile information in your dashboard and contact support to request access, correction, or deletion where applicable law allows. Some records must be kept for compliance even after account closure.',
                    ],
                ],
                [
                    'id' => 'privacy-contact',
                    'nav' => '7. Contact',
                    'title' => 'Contact',
                    'number' => '07',
                    'variant' => 'contact',
                    'paragraphs' => [
                        'Privacy questions can be raised through a support ticket on the Platform.',
                    ],
                ],
            ],
        ],
    ],
];
