<?php

return [
    'slug' => 'buying-selling-marketplace',
    'category_key' => 'marketplace',
    'title' => 'Buying and Selling in the Marketplace',
    'intro' => 'Browse listings, buy with escrow, sell as a vendor, manage orders, and build reputation with reviews.',
    'summary' => 'The marketplace connects buyers and sellers with search, favorites, escrow checkout, and delivery confirmation.',
    'updated_at' => '2026-07-20',
    'printable' => true,
    'related' => ['keeping-account-secure', 'billing-wallets-payments', 'getting-started'],
    'platform_actions' => [
        ['label' => 'Marketplace', 'route' => 'marketplace'],
        ['label' => 'My listings', 'route' => 'dashboard.listings', 'auth' => true],
        ['label' => 'Wallet', 'route' => 'dashboard.wallet', 'auth' => true],
    ],
    'sections' => [
        [
            'id' => 'browse',
            'nav' => 'Browsing listings',
            'title' => 'Browsing listings',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Open Marketplace to explore published listings. Use cards and detail pages to compare price, description, and seller info.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Marketplace',
                    'caption' => 'Marketplace grid of active listings.',
                    'size' => 'large',
                    'alignment' => 'full',
                    'alt' => 'Marketplace page screenshot',
                ],
            ],
        ],
        [
            'id' => 'search',
            'nav' => 'Searching',
            'title' => 'Searching',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Use the marketplace search box to find listings by keyword. Suggestions may appear as you type.'],
            ],
        ],
        [
            'id' => 'filters',
            'nav' => 'Filters',
            'title' => 'Filters',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Apply category and other filters (when available) to narrow results to what you need.'],
            ],
        ],
        [
            'id' => 'favorites',
            'nav' => 'Favorites',
            'title' => 'Favorites / watchlist',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Save interesting listings to your watchlist so you can return quickly from the dashboard.'],
            ],
        ],
        [
            'id' => 'product-pages',
            'nav' => 'Product pages',
            'title' => 'Product pages',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Listing pages show media, price, description, and Buy Now for escrow checkout when you are signed in.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Listing page',
                    'caption' => 'Individual listing with purchase CTA.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Marketplace listing page screenshot',
                ],
            ],
        ],
        [
            'id' => 'escrow-checkout',
            'nav' => 'Escrow checkout',
            'title' => 'Escrow checkout',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Buying holds NGN from your wallet in escrow until you confirm delivery. Ensure your wallet is funded first.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Checkout',
                    'caption' => 'Escrow checkout confirmation for a listing.',
                    'size' => 'medium',
                    'alignment' => 'center',
                    'alt' => 'Marketplace checkout screenshot',
                ],
            ],
        ],
        [
            'id' => 'confirm-delivery',
            'nav' => 'Confirming delivery',
            'title' => 'Confirming delivery',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'When you receive what you ordered, confirm delivery so escrow can release to the seller. Raise a dispute/support ticket if something is wrong.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Orders',
                    'caption' => 'Buyer orders with delivery confirmation actions.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Buyer orders screenshot',
                ],
            ],
        ],
        [
            'id' => 'reviews',
            'nav' => 'Reviews',
            'title' => 'Reviews',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'After a successful order you can leave a review to help other buyers and build transparent reputation.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Reviews',
                    'caption' => 'Leave or view ratings on completed trades.',
                    'size' => 'medium',
                    'alignment' => 'center',
                    'alt' => 'Reviews screenshot',
                ],
            ],
        ],
        [
            'id' => 'become-vendor',
            'nav' => 'Becoming a vendor',
            'title' => 'Becoming a vendor',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Signed-in users can create listings from the dashboard. Complete profile and KYC requirements that apply to sellers on the platform.'],
            ],
        ],
        [
            'id' => 'create-listings',
            'nav' => 'Creating listings',
            'title' => 'Creating listings',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Provide a clear title, description, price, and media. New or edited listings may require admin approval before they appear publicly.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Create listing',
                    'caption' => 'Dashboard form to create a marketplace listing.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Create listing screenshot',
                ],
            ],
        ],
        [
            'id' => 'manage-listings',
            'nav' => 'Managing listings',
            'title' => 'Managing listings',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Edit, pause, or update listings from My Listings. Major edits may create a new version for review.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Seller dashboard',
                    'caption' => 'Seller listing management in the dashboard.',
                    'size' => 'large',
                    'alignment' => 'center',
                    'alt' => 'Seller dashboard screenshot',
                ],
            ],
        ],
        [
            'id' => 'receiving-orders',
            'nav' => 'Receiving orders',
            'title' => 'Receiving orders',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'When a buyer pays into escrow, fulfill promptly and communicate via platform messages when needed.'],
            ],
        ],
        [
            'id' => 'seller-escrow',
            'nav' => 'Escrow process (sellers)',
            'title' => 'Escrow process for sellers',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Funds stay in escrow until the buyer confirms delivery (or support resolves a case). Platform fees may apply on release.'],
            ],
        ],
        [
            'id' => 'deliveries',
            'nav' => 'Completing deliveries',
            'title' => 'Completing deliveries',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Deliver exactly what was listed. Keep proof of delivery for disputes.'],
            ],
        ],
        [
            'id' => 'reputation',
            'nav' => 'Seller reputation',
            'title' => 'Building seller reputation',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Reliable delivery and fair communication lead to better reviews and more sales over time.'],
                [
                    'type' => 'screenshot',
                    'title' => 'Buyer dashboard',
                    'caption' => 'Buyer view of orders and activity.',
                    'size' => 'medium',
                    'alignment' => 'center',
                    'alt' => 'Buyer dashboard screenshot',
                ],
            ],
        ],
        [
            'id' => 'faqs',
            'nav' => 'FAQs',
            'title' => 'Marketplace FAQs',
            'blocks' => [
                [
                    'type' => 'faq',
                    'items' => [
                        ['q' => 'Why is my listing not visible?', 'a' => 'It may be pending admin review or unpublished. Check listing status in the dashboard.'],
                        ['q' => 'When do sellers get paid?', 'a' => 'After the buyer confirms delivery (or support releases escrow), minus any platform fee.'],
                        ['q' => 'Can I cancel an escrow order?', 'a' => 'Contact support with your order ID. Policies depend on delivery status.'],
                    ],
                ],
            ],
        ],
    ],
];
