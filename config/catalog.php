<?php

return [

    'types' => [
        'website_template' => [
            'label' => 'Website Templates',
            'icon' => 'listings',
            'default_route' => 'website-listings',
        ],
        'website_package' => [
            'label' => 'Website Packages',
            'icon' => 'inventory',
            'default_route' => 'website-listings',
        ],
        'document_template' => [
            'label' => 'Document Templates',
            'icon' => 'listings',
            'default_route' => 'templates',
        ],
        'virtual_phone' => [
            'label' => 'Virtual Phone Numbers',
            'icon' => 'support',
            'default_route' => 'services',
        ],
        'vpn' => [
            'label' => 'VPN',
            'icon' => 'lock',
            'default_route' => 'services',
        ],
        'vps' => [
            'label' => 'VPS',
            'icon' => 'monitoring',
            'default_route' => 'services',
        ],
        'proxy' => [
            'label' => 'Proxy',
            'icon' => 'tune',
            'default_route' => 'services',
        ],
        'smtp' => [
            'label' => 'SMTP',
            'icon' => 'messages',
            'default_route' => 'services',
        ],
        'domain' => [
            'label' => 'Domains',
            'icon' => 'storefront',
            'default_route' => 'services',
        ],
        'email' => [
            'label' => 'Email Services',
            'icon' => 'messages',
            'default_route' => 'services',
        ],
        'social_service' => [
            'label' => 'Social Media Services',
            'icon' => 'analytics',
            'default_route' => 'services',
        ],
        'escrow_service' => [
            'label' => 'Escrow Service',
            'icon' => 'lock',
            'default_route' => 'services',
        ],
    ],

    /*
    | UI-only business divisions for the Services page.
    | Not stored in the database.
    */
    'divisions' => [
        'digital-services' => [
            'label' => 'Digital Services',
            'description' => 'VPN, VPS, proxies, SMTP, phone numbers, email, and social growth.',
            'types' => ['vpn', 'vps', 'proxy', 'smtp', 'virtual_phone', 'email', 'social_service'],
        ],
        'web-solutions' => [
            'label' => 'Web Solutions',
            'description' => 'Website templates, hosted packages, and domains.',
            'types' => ['website_template', 'website_package', 'domain'],
        ],
        'business-documents' => [
            'label' => 'Business Documents',
            'description' => 'Contracts, agreements, and ready-to-edit templates.',
            'types' => ['document_template'],
        ],
        'trust-protection' => [
            'label' => 'Trust & Protection',
            'description' => 'Escrow and protected trade services.',
            'types' => ['escrow_service'],
        ],
    ],
];
