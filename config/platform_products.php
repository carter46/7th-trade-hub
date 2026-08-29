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
    ],

    'domain' => [
        'com-domain-registration',
        'io-domain-registration',
        'co-domain-registration',
    ],
    'website_package' => [
        'starter-business-site',
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
    'website_template' => [],
    'vps' => [],
];
