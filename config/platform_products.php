<?php

/**
 * Canonical platform product slugs per service type.
 * Empty array = remove every product under that type.
 * retired_services = entire service rows removed from catalog after products are deleted.
 */
return [
    'retired_services' => [
        'website_template',
        'vps',
        'document_template',
    ],

    'domain' => [
        'domain-registration',
    ],
    'website_package' => [
        'online-banking-website',
    ],
    'vpn' => [
        'dedicated-ip-vpn',
    ],
    'proxy' => [
        'isp-proxy-bundle',
    ],
    'smtp' => [
        'dedicated-smtp-ip',
    ],
    'email' => [
        'business-email-starter',
    ],
    'virtual_phone' => [
        'us-virtual-number',
        'uk-virtual-number',
        'sms-ready-number',
    ],
    'document_template' => [],
    'receipt' => [
        'invoice-receipt-set',
        'payment-receipt-template',
        'sales-receipt-pack',
    ],
    'document' => [
        'employment-agreement',
        'nda-bundle',
        'sales-contract-pack',
    ],
    'social_service' => [
        'instagram-growth-pack',
        'tiktok-engagement-boost',
        'youtube-views-lite',
        'twitter-audience-pack',
        'facebook-growth-pack',
    ],
    'website_template' => [],
    'vps' => [],
];
