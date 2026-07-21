<?php

return [

    'types' => [
        'website_template' => [
            'label' => 'Website Templates',
            'icon' => 'listings',
            'default_route' => 'website-listings',
            'short_description' => 'Ready-to-launch website designs for businesses and creators.',
            'hero_title' => 'Website Templates',
            'hero_subtitle' => 'Browse polished templates you can adapt to your brand.',
            'benefits' => [
                'Modern layouts ready for customization',
                'Responsive across devices',
                'Clear handoff for developers or DIY edits',
            ],
            'faq' => [
                ['q' => 'Can I edit the template after purchase?', 'a' => 'Yes. You receive files or access details so you can customize the design.'],
                ['q' => 'Is hosting included?', 'a' => 'Templates are design assets. Hosting is separate unless noted on the product.'],
            ],
        ],
        'website_package' => [
            'label' => 'Website Packages',
            'icon' => 'inventory',
            'default_route' => 'website-listings',
            'short_description' => 'Hosted website packages with demos and support windows.',
            'hero_title' => 'Website Packages',
            'hero_subtitle' => 'Pick a package with demo access and clear deliverables.',
            'benefits' => [
                'Demo environments to preview before you buy',
                'Defined support period on eligible packages',
                'Business-ready industry options',
            ],
            'faq' => [
                ['q' => 'How do demos work?', 'a' => 'Eligible packages include a demo URL and login so you can explore before checkout.'],
            ],
        ],
        'document_template' => [
            'label' => 'Document Templates',
            'icon' => 'listings',
            'default_route' => 'templates',
            'short_description' => 'Contracts, HR, and legal templates ready to customize.',
            'hero_title' => 'Business Document Templates',
            'hero_subtitle' => 'Filter by Contracts, HR, or Legal and download what you need.',
            'benefits' => [
                'Structured templates for common business needs',
                'Clear category filters',
                'Editable after purchase',
            ],
            'faq' => [
                ['q' => 'Are these legal advice?', 'a' => 'No. Templates are starting points. Have a qualified professional review critical documents.'],
            ],
        ],
        'virtual_phone' => [
            'label' => 'Virtual Phone Numbers',
            'icon' => 'support',
            'default_route' => 'services',
            'short_description' => 'Local and international virtual numbers for business and verification.',
            'hero_title' => 'Virtual Phone Numbers',
            'hero_subtitle' => 'Choose regions and plans that fit your workflow.',
            'benefits' => [
                'Multiple regions available',
                'Flexible plan durations',
                'Clear pricing before checkout',
            ],
            'faq' => [
                ['q' => 'How quickly is a number provisioned?', 'a' => 'Most orders are fulfilled after payment confirmation according to the product notes.'],
            ],
        ],
        'vpn' => [
            'label' => 'VPN',
            'icon' => 'lock',
            'default_route' => 'services',
            'short_description' => 'Residential, gaming, and business VPN plans.',
            'hero_title' => 'VPN Services',
            'hero_subtitle' => 'Secure connections with plans for homes, teams, and gaming.',
            'benefits' => [
                'Multiple plan tiers',
                'Clear duration and pricing',
                'Escrow-backed platform checkout',
            ],
            'faq' => [
                ['q' => 'Which VPN should I pick?', 'a' => 'Use category filters (Residential, Gaming, Business) or compare featured plans below.'],
            ],
        ],
        'vps' => [
            'label' => 'VPS',
            'icon' => 'monitoring',
            'default_route' => 'services',
            'short_description' => 'Shared and dedicated VPS options for apps and sites.',
            'hero_title' => 'VPS Hosting',
            'hero_subtitle' => 'Scale from shared to dedicated resources as you grow.',
            'benefits' => [
                'Shared and dedicated tiers',
                'Transparent monthly pricing',
                'Suitable for apps, sites, and automation',
            ],
            'faq' => [
                ['q' => 'Is management included?', 'a' => 'Check each product description for managed vs self-managed details.'],
            ],
        ],
        'proxy' => [
            'label' => 'Proxy',
            'icon' => 'tune',
            'default_route' => 'services',
            'short_description' => 'Datacenter, residential, and mobile proxy pools.',
            'hero_title' => 'Proxy Services',
            'hero_subtitle' => 'Pick the proxy type that matches your use case.',
            'benefits' => [
                'Datacenter, residential, and mobile options',
                'Plan-based pricing',
                'Filter by category quickly',
            ],
            'faq' => [
                ['q' => 'Can I switch plans later?', 'a' => 'Purchase a new plan that fits; contact support if you need help migrating.'],
            ],
        ],
        'smtp' => [
            'label' => 'SMTP',
            'icon' => 'messages',
            'default_route' => 'services',
            'short_description' => 'Transactional and marketing SMTP capacity.',
            'hero_title' => 'SMTP Services',
            'hero_subtitle' => 'Reliable outbound email for transactional and marketing flows.',
            'benefits' => [
                'Transactional and marketing tiers',
                'Clear send volume expectations on plans',
                'Platform-protected checkout',
            ],
            'faq' => [
                ['q' => 'Do you provide warm-up guidance?', 'a' => 'Product pages note recommended use; follow your own compliance and deliverability practices.'],
            ],
        ],
        'domain' => [
            'label' => 'Domains',
            'icon' => 'storefront',
            'default_route' => 'services',
            'short_description' => 'Domain registration and transfer assistance.',
            'hero_title' => 'Domain Services',
            'hero_subtitle' => 'Register or transfer domains with clear next steps.',
            'benefits' => [
                'Registration and transfer options',
                'Straightforward pricing',
                'Guided fulfillment after payment',
            ],
            'faq' => [
                ['q' => 'Who owns the domain after purchase?', 'a' => 'Ownership and registrar details are confirmed during fulfillment for your order.'],
            ],
        ],
        'email' => [
            'label' => 'Email Services',
            'icon' => 'messages',
            'default_route' => 'services',
            'short_description' => 'Business and team email mailboxes.',
            'hero_title' => 'Email Services',
            'hero_subtitle' => 'Professional mailboxes for solo operators and teams.',
            'benefits' => [
                'Business and team plans',
                'Predictable pricing',
                'Works alongside your domain setup',
            ],
            'faq' => [
                ['q' => 'Can I use my own domain?', 'a' => 'Yes on eligible plans — see product requirements for DNS setup.'],
            ],
        ],
        'social_service' => [
            'label' => 'Social Media Services',
            'icon' => 'analytics',
            'default_route' => 'services',
            'short_description' => 'Growth and engagement services for social platforms.',
            'hero_title' => 'Social Media Services',
            'hero_subtitle' => 'Filter Growth or Engagement packages for the outcome you want.',
            'benefits' => [
                'Growth and engagement categories',
                'Clear deliverable descriptions',
                'Protected platform checkout',
            ],
            'faq' => [
                ['q' => 'How do I choose Growth vs Engagement?', 'a' => 'Use the category filter: Growth focuses on audience size; Engagement focuses on interaction.'],
            ],
        ],
        'escrow_service' => [
            'label' => 'Escrow Service',
            'icon' => 'lock',
            'default_route' => 'services',
            'short_description' => 'Protected trade escrow for high-value digital deals.',
            'hero_title' => 'Escrow Services',
            'hero_subtitle' => 'Add trust to peer deals with platform-backed escrow options.',
            'benefits' => [
                'Standard and high-value tiers',
                'Clear fee structure on each product',
                'Aligned with marketplace escrow flows',
            ],
            'faq' => [
                ['q' => 'Is escrow automatic on marketplace buys?', 'a' => 'Eligible marketplace purchases use escrow by default. These products cover additional escrow needs.'],
            ],
        ],
    ],

    /*
    | User-facing service groups (primary navigation on /services).
    | Defaults can be overridden via catalog_page_contents (scope=group).
    */
    'groups' => [
        'network-services' => [
            'label' => 'Network Services',
            'banner_image' => 'assets/images/Network Services_1.jpg',
            'card_image' => 'assets/images/Network Services_1.jpg',
            'short_description' => 'VPN, VPS, SMTP, and proxy plans for connectivity and infrastructure.',
            'hero_title' => 'Network Services',
            'hero_subtitle' => 'Secure connections, servers, mail relay, and proxies in one place.',
            'benefits' => [
                'Infrastructure and connectivity in one browse path',
                'Compare plans by type before you buy',
                'Transparent NGN pricing',
            ],
            'faq' => [
                ['q' => 'Where do I start?', 'a' => 'Pick a type card below (VPN, VPS, SMTP, or Proxy), then filter plans on the type page.'],
            ],
            'types' => ['vpn', 'vps', 'smtp', 'proxy'],
        ],
        'communication' => [
            'label' => 'Communication',
            'banner_image' => 'assets/images/Communication_1.jpg',
            'card_image' => 'assets/images/Communication_1.jpg',
            'short_description' => 'Email mailboxes and virtual phone numbers for business outreach.',
            'hero_title' => 'Communication',
            'hero_subtitle' => 'Stay reachable with professional email and virtual numbers.',
            'benefits' => [
                'Email and phone in one group',
                'Plan options for solo and team use',
                'Clear checkout on the platform',
            ],
            'faq' => [],
            'types' => ['email', 'virtual_phone'],
        ],
        'social-media' => [
            'label' => 'Social Media',
            'banner_image' => 'assets/images/Social_Media.jpg',
            'card_image' => 'assets/images/Social_Media.jpg',
            'short_description' => 'Growth and engagement services for social platforms.',
            'hero_title' => 'Social Media',
            'hero_subtitle' => 'Browse social services, then filter by Growth or Engagement.',
            'benefits' => [],
            'faq' => [],
            'types' => ['social_service'],
        ],
        'website-services' => [
            'label' => 'Website Services',
            'banner_image' => 'assets/images/Website_Services.jpg',
            'card_image' => 'assets/images/Website_Services.jpg',
            'short_description' => 'Templates, hosted packages, and domain services.',
            'hero_title' => 'Website Services',
            'hero_subtitle' => 'From design templates to domains — build your web presence.',
            'benefits' => [
                'Templates, packages, and domains together',
                'Demos on eligible packages',
                'Straightforward next steps after purchase',
            ],
            'faq' => [],
            'types' => ['website_template', 'website_package', 'domain'],
        ],
        'business-documents' => [
            'label' => 'Documents & Receipts',
            'banner_image' => 'assets/images/Business_Documents.jpg',
            'card_image' => 'assets/images/Business_Documents.jpg',
            'short_description' => 'Contracts, HR, and legal document templates.',
            'hero_title' => 'Documents & Receipts',
            'hero_subtitle' => 'Ready-to-edit templates for everyday business paperwork.',
            'benefits' => [
                'Contracts, HR, and Legal categories',
                'Quick filters on the type page',
                'Editable after purchase',
            ],
            'faq' => [],
            'types' => ['document_template'],
        ],
        'trust-escrow' => [
            'label' => 'Trust & Escrow',
            'banner_image' => 'assets/images/flat-lay-real-estate-concept.jpg',
            'card_image' => 'assets/images/flat-lay-real-estate-concept.jpg',
            'short_description' => 'Buy and sell digital products with marketplace escrow protection.',
            'hero_title' => 'Trust & Escrow',
            'hero_subtitle' => 'Explore escrow-protected purchases in the marketplace.',
            'benefits' => [
                'Aligned with marketplace protection',
                'Funds held until delivery confirmation',
            ],
            'faq' => [],
            'types' => [],
            'route' => 'marketplace',
            'cta' => 'Open marketplace',
        ],
    ],

    /*
    | @deprecated Use groups. Kept for any leftover references during transition.
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
            'label' => 'Documents & Receipts',
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
