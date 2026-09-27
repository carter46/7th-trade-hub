<?php

return [
    'updated_at' => '2026-09-27',

    'contact' => [
        'email' => env('LEGAL_CONTACT_EMAIL'),
    ],

    'documents' => [
        'terms' => [
            'label' => 'Terms of Service',
            'eyebrow' => 'Compliance & Legal',
            'intro' => 'Please review the rules for using 7th Trade Hub and its digital services and products.',
            'summary' => 'By using 7th Trade Hub you agree to our platform rules, KYC requirements where applicable, and wallet and checkout terms. This document was last updated September 2026.',
            'sections' => [
                [
                    'id' => 'acceptance',
                    'nav' => '1. Acceptance of Terms',
                    'title' => 'Acceptance of Terms',
                    'number' => '01',
                    'paragraphs' => [
                        'By accessing or using 7th Trade Hub (“the Platform”), including the digital services catalog and Naira wallet, you confirm that you have read, understood, and agree to these Terms of Service.',
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
                    'id' => 'services',
                    'nav' => '3. Platform Services',
                    'title' => 'Platform Services',
                    'number' => '03',
                    'paragraphs' => [
                        'The Platform lets you browse and purchase digital services operated by 7th Trade Hub.',
                    ],
                    'cards' => [
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
                        'Wallet funding, withdrawals, and checkout are subject to verification, admin review where required, and applicable fees.',
                    ],
                    'bullets' => [
                        'You are responsible for providing correct bank details. We are not liable for losses from incorrect details you supply.',
                        'Deposits and withdrawals may take from minutes up to longer review windows depending on method and compliance checks.',
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
                        'Fraud, phishing, or impersonation',
                        'Money laundering or illegal payments',
                        'Payment abuse or chargeback fraud',
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
                        'To the fullest extent permitted by law, 7th Trade Hub is not liable for indirect, incidental, or consequential damages arising from use of the Platform, including delays in funding, order fulfilment, or third-party network issues.',
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
            'intro' => 'How 7th Trade Hub collects, uses, and protects personal data across wallet, KYC, orders, and support workflows.',
            'summary' => 'We collect account, transaction, and KYC data needed to run the Platform, prevent fraud, and meet legal obligations. We do not sell your personal data. This policy was last updated September 2026.',
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
                        'Transaction records (deposits, withdrawals, orders, and support tickets).',
                        'Technical logs such as IP address, device/browser data, and security events.',
                    ],
                ],
                [
                    'id' => 'use',
                    'nav' => '2. How We Use Data',
                    'title' => 'How We Use Data',
                    'number' => '02',
                    'paragraphs' => [
                        'We use personal data to operate and secure the Platform, process payments, complete KYC, respond to support requests, improve services, and comply with applicable law.',
                    ],
                ],
                [
                    'id' => 'sharing',
                    'nav' => '3. Sharing',
                    'title' => 'Sharing',
                    'number' => '03',
                    'paragraphs' => [
                        'We share data only as needed with infrastructure and service providers (for example hosting, email, domain registrars, and payment processors) as needed to fulfil an order you place, or when required by law or to protect the Platform and users.',
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
