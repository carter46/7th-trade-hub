<?php

return [

    'disk' => env('MEDIA_DISK', 'public'),

    'directory' => 'media',

    /** Max upload size in kilobytes (2048 = 2 MB). */
    'max_upload_size_kb' => (int) env('MEDIA_MAX_UPLOAD_KB', 2048),

    'allowed_mimes' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ],

    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],

    /** Only these MediaType values are accepted for uploads. */
    'allowed_types' => ['image'],

    'max_dimensions' => [
        'width' => 8000,
        'height' => 8000,
    ],

    'keep_original' => (bool) env('MEDIA_KEEP_ORIGINAL', false),

    'strip_exif' => true,

    'output_format' => 'webp',

    'jpeg_quality' => 82,

    'webp_quality' => 80,

    /**
     * Optional brand/tenant key for white-label deployments.
     * When set, new uploads can be tagged with this brand_key.
     */
    'brand_key' => env('MEDIA_BRAND_KEY'),

    /** Default collection label for uncategorized library uploads. */
    'default_collection' => env('MEDIA_DEFAULT_COLLECTION', 'library'),

    'derivatives' => [
        'large' => ['max_width' => 1920],
        'medium' => ['max_width' => 1200],
        'small' => ['max_width' => 640],
        'thumbnail' => ['max_width' => 300, 'max_height' => 300, 'crop' => true],
    ],

    /** Private document uploads (deposit proofs, future KYC) — not Media Library grid. */
    'documents' => [
        'disk' => env('MEDIA_DOCUMENTS_DISK', 'local'),
        'directory' => 'documents',
        'max_upload_size_kb' => (int) env('MEDIA_DOCUMENTS_MAX_UPLOAD_KB', 5120),
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'application/pdf',
        ],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
    ],

];
