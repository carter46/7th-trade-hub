<?php

/**
 * Fixed platform categories — existence source of truth.
 * Public CMS (name, images, etc.) lives in service_categories rows.
 * Do not overwrite DB content from this file.
 */
return [
    'website' => [
        'slug' => 'website-services',
        'expected_id' => 4,
    ],
    'social' => [
        'slug' => 'social-media',
        'expected_id' => 3,
    ],
    'network' => [
        'slug' => 'network-services',
        'expected_id' => 1,
    ],
    'communication' => [
        'slug' => 'communication',
        'expected_id' => 2,
    ],
    'documentary' => [
        'slug' => 'business-documents',
        'expected_id' => 5,
    ],
    'trust_escrow' => [
        'slug' => 'trust-escrow',
        'expected_id' => 6,
    ],
];
