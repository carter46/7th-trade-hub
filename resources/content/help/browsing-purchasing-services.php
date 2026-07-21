<?php

return [
    'slug' => 'browsing-purchasing-services',
    'category_key' => 'services',
    'title' => 'Browsing and Purchasing Platform Services',
    'intro' => 'Explore service categories, filter products, choose variants, and check out with your wallet.',
    'summary' => 'Platform services cover network tools, social growth, websites, documents, and more — purchased from your funded Naira wallet.',
    'updated_at' => '2026-07-20',
    'hero_image' => 'assets/images/services_1.jpg',
    'printable' => true,
    'related' => ['billing-wallets-payments', 'getting-started'],
    'platform_actions' => [
        ['label' => 'Browse services', 'route' => 'services'],
        ['label' => 'Wallet', 'route' => 'dashboard.wallet', 'auth' => true],
        ['label' => 'Help Center', 'route' => 'help'],
    ],
    'sections' => [
        [
            'id' => 'available',
            'nav' => 'What services are available',
            'title' => 'What services are available',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'The Services catalog groups products such as VPN/VPS, proxies, social services, virtual numbers, email, websites, and related digital offerings. Categories on the Services page reflect what is currently published.'],
            ],
        ],
        [
            'id' => 'browse',
            'nav' => 'Browsing categories',
            'title' => 'Browsing service categories',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Start at Services to see category cards, then open a group or type to view products.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Services page',
                    'caption' => 'Main Services hub with category groups.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Services page screenshot',
                ],
                [
                    'type' => 'screenshot',
                    'title' => 'Category page',
                    'caption' => 'Products within a selected service category.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Service category page screenshot',
                ],
            ],
        ],
        [
            'id' => 'filters',
            'nav' => 'Filtering services',
            'title' => 'Filtering services',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'On category pages, use filters (when shown) to narrow by attributes such as plan size or product type so you can find the right offering faster.'],
            ],
        ],
        [
            'id' => 'details',
            'nav' => 'Viewing product details',
            'title' => 'Viewing product details',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Open a product to read the description, pricing, and delivery notes before you buy.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Product page',
                    'caption' => 'Product buy box with details and purchase CTA.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Service product page screenshot',
                ],
            ],
        ],
        [
            'id' => 'variants',
            'nav' => 'Choosing variants',
            'title' => 'Choosing variants',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Some products offer plans or variants (duration, capacity, or tier). Select the option that matches your need before clicking Buy Now.'],
                ['type' => 'tip', 'title' => 'Compare carefully', 'content' => 'Higher tiers usually cost more but include more capacity or longer validity — check the product copy.'],
            ],
        ],
        [
            'id' => 'checkout',
            'nav' => 'Checkout',
            'title' => 'Checkout',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Buy Now takes you to authenticated checkout. Payment is taken from your Naira wallet balance. Fund the wallet first if your balance is too low.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Checkout',
                    'caption' => 'Confirm order and pay from your wallet.',
                    'size' => 'medium',
                    'alignment' => 'center',
                    'alt' => 'Service checkout screenshot',
                ],
            ],
        ],
        [
            'id' => 'purchases',
            'nav' => 'Viewing purchases',
            'title' => 'Viewing purchases',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Completed purchases appear in your orders / purchase history in the dashboard.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Orders page',
                    'caption' => 'Dashboard orders list for services and marketplace buys.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Orders page screenshot',
                ],
            ],
        ],
        [
            'id' => 'access',
            'nav' => 'Accessing purchased services',
            'title' => 'Accessing purchased services',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Delivery details (credentials, links, or fulfillment notes) are shown on the order or product delivery area after payment succeeds. Keep them private and store them securely.'],
                ['type' => 'warning', 'title' => 'Protect delivery details', 'content' => 'Never share login credentials from a purchase in public chats or with unknown contacts.'],
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
                        ['q' => 'Do I need a wallet balance to buy?', 'a' => 'Yes. Fund via bank deposit or crypto sell, then return to checkout.'],
                        ['q' => 'Can guests browse services?', 'a' => 'Yes. Checkout requires an account and sufficient wallet balance.'],
                        ['q' => 'Where do I get help with a failed order?', 'a' => 'Open a support ticket or use Contact Us with your order reference.'],
                    ],
                ],
            ],
        ],
    ],
];
