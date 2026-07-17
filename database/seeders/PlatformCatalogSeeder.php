<?php

namespace Database\Seeders;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Models\PlatformCategory;
use App\Models\PlatformProduct;
use App\Models\PlatformProductImage;
use App\Models\PlatformProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlatformCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            PlatformProductType::Vpn->value => [
                'Residential VPN Pro', 'Business VPN Shield', 'Gaming VPN Boost',
                'Dedicated IP VPN', 'Family VPN Pack', 'Travel VPN Lite',
            ],
            PlatformProductType::Vps->value => [
                'Starter VPS 1GB', 'Growth VPS 2GB', 'Pro VPS 4GB',
                'Business VPS 8GB', 'High CPU VPS', 'Storage VPS 100GB',
            ],
            PlatformProductType::Proxy->value => [
                'Datacenter Proxy Pack', 'Residential Proxy 1GB', 'Mobile Proxy Pool',
                'ISP Proxy Bundle', 'Sticky Session Proxy', 'Rotating Proxy Lite',
            ],
            PlatformProductType::Smtp->value => [
                'SMTP Starter 10k', 'SMTP Growth 50k', 'SMTP Pro 200k',
                'Transactional SMTP', 'Marketing SMTP', 'Dedicated SMTP IP',
            ],
            PlatformProductType::VirtualPhone->value => [
                'US Virtual Number', 'UK Virtual Number', 'NG Virtual Number',
                'Business Line Bundle', 'SMS-Ready Number', 'Toll-Free Lite',
            ],
            PlatformProductType::Email->value => [
                'Business Email Starter', 'Team Email 5 Seats', 'Custom Domain Email',
                'Secure Mail Pro', 'Catch-All Mailbox', 'Email Forwarding Pack',
            ],
            PlatformProductType::SocialService->value => [
                'Instagram Growth Pack', 'TikTok Engagement Boost', 'YouTube Views Lite',
                'Twitter Audience Pack', 'LinkedIn Lead Boost', 'Multi-Platform Starter',
            ],
            PlatformProductType::Domain->value => [
                '.com Domain Registration', '.ng Domain Registration', '.io Domain Registration',
                '.co Domain Registration', 'Domain Transfer Assist', 'Domain Privacy Pack',
            ],
            PlatformProductType::EscrowService->value => [
                'Standard Escrow Trade', 'High-Value Escrow', 'Website Sale Escrow',
                'Account Transfer Escrow', 'Milestone Escrow', 'Express Escrow',
            ],
            PlatformProductType::WebsiteTemplate->value => [
                'Corporate Landing Kit', 'Agency Portfolio Theme', 'Law Firm Site Kit',
                'Restaurant Menu Theme', 'Medical Clinic Theme', 'Startup Launch Template',
            ],
            PlatformProductType::WebsitePackage->value => [
                'Starter Business Site', 'Agency Showcase Site', 'Restaurant Booking Site',
                'Law Practice Site', 'Clinic Booking Site', 'E-commerce Starter Site',
            ],
            PlatformProductType::DocumentTemplate->value => [
                'Sales Contract Pack', 'NDA Bundle', 'Employment Agreement',
                'Invoice & Receipt Set', 'HR Policy Pack', 'Service Level Agreement',
            ],
        ];

        $categoryMap = [
            PlatformProductType::WebsiteTemplate->value => ['wt-corporate', 'wt-agency', 'wt-law', 'wt-restaurant', 'wt-medical', 'wt-corporate'],
            PlatformProductType::WebsitePackage->value => ['wp-starter', 'wp-business', 'wp-ecommerce', 'wp-starter', 'wp-business', 'wp-ecommerce'],
            PlatformProductType::DocumentTemplate->value => ['dt-legal', 'dt-legal', 'dt-hr', 'dt-business', 'dt-hr', 'dt-legal'],
            PlatformProductType::Vpn->value => ['vpn-residential', 'vpn-business', 'vpn-gaming', 'vpn-dedicated', 'vpn-residential', 'vpn-business'],
            PlatformProductType::VirtualPhone->value => ['phone-us', 'phone-uk', 'phone-ng', 'phone-us', 'phone-us', 'phone-uk'],
            PlatformProductType::Vps->value => ['vps-shared', 'vps-shared', 'vps-dedicated', 'vps-dedicated', 'vps-dedicated', 'vps-shared'],
            PlatformProductType::Proxy->value => ['proxy-datacenter', 'proxy-residential', 'proxy-mobile', 'proxy-residential', 'proxy-datacenter', 'proxy-mobile'],
            PlatformProductType::Smtp->value => ['smtp-transactional', 'smtp-marketing', 'smtp-marketing', 'smtp-transactional', 'smtp-marketing', 'smtp-transactional'],
            PlatformProductType::Email->value => ['email-business', 'email-team', 'email-business', 'email-team', 'email-business', 'email-team'],
            PlatformProductType::SocialService->value => ['social-growth', 'social-engagement', 'social-growth', 'social-engagement', 'social-growth', 'social-growth'],
            PlatformProductType::Domain->value => ['domain-registration', 'domain-registration', 'domain-registration', 'domain-registration', 'domain-transfer', 'domain-registration'],
            PlatformProductType::EscrowService->value => ['escrow-standard', 'escrow-high-value', 'escrow-high-value', 'escrow-standard', 'escrow-standard', 'escrow-standard'],
        ];

        foreach ($catalog as $type => $titles) {
            foreach ($titles as $i => $title) {
                $slug = Str::slug($title);
                $categoryId = null;
                if (isset($categoryMap[$type][$i])) {
                    $categoryId = PlatformCategory::where('slug', $categoryMap[$type][$i])->value('id');
                }

                $base = 5000 + ($i * 2500);
                if (in_array($type, [PlatformProductType::WebsitePackage->value, PlatformProductType::Vps->value], true)) {
                    $base = 15000 + ($i * 5000);
                }

                $product = PlatformProduct::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'platform_category_id' => $categoryId,
                        'product_type' => $type,
                        'title' => $title,
                        'short_description' => "Ready-to-use {$title} from 7th Trade Hub.",
                        'description' => "Get started quickly with {$title}. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.",
                        'status' => PlatformProductStatus::Published,
                        'is_featured' => $i < 2,
                        'sort_order' => $i,
                        'hero_image' => null,
                        'demo_url' => $type === PlatformProductType::WebsitePackage->value ? 'https://example.com/demo/'.$slug : null,
                        'demo_username' => $type === PlatformProductType::WebsitePackage->value ? 'demo@7thtrade.local' : null,
                        'demo_password' => $type === PlatformProductType::WebsitePackage->value ? 'DemoPass123!' : null,
                        'industry' => $type === PlatformProductType::WebsitePackage->value ? ['Business', 'Agency', 'Food', 'Legal', 'Health', 'Retail'][$i] : null,
                        'framework' => $type === PlatformProductType::WebsitePackage->value ? ['Laravel', 'WordPress', 'Next.js', 'Laravel', 'WordPress', 'Shopify'][$i] : null,
                        'is_responsive' => true,
                        'is_seo_ready' => $type === PlatformProductType::WebsitePackage->value,
                        'support_period' => $type === PlatformProductType::WebsitePackage->value ? '30 days' : null,
                        'features' => ['Fast setup', 'NGN wallet checkout', 'Email support'],
                        'requirements' => ['Active 7th Trade Hub account', 'Funded wallet for purchase'],
                        'whats_included' => ['Product access', 'Basic setup guide', 'Support window'],
                        'faqs' => [
                            ['q' => 'How fast is delivery?', 'a' => 'Most digital products are available right after payment.'],
                            ['q' => 'Can I get a refund?', 'a' => 'Refunds follow our support policy for unused digital goods.'],
                        ],
                        'support_text' => 'Open a support ticket from your dashboard if you need help.',
                        'base_price' => $base,
                        'meta' => null,
                    ]
                );

                $this->seedVariants($product, $type, (float) $product->base_price);
                $this->seedGallery($product);
            }
        }
    }

    private function seedVariants(PlatformProduct $product, string $type, float $base): void
    {
        $needsDuration = in_array($type, [
            PlatformProductType::WebsitePackage->value,
            PlatformProductType::Vpn->value,
            PlatformProductType::Vps->value,
            PlatformProductType::VirtualPhone->value,
            PlatformProductType::Email->value,
            PlatformProductType::Smtp->value,
            PlatformProductType::Proxy->value,
        ], true);

        if (! $needsDuration) {
            PlatformProductVariant::updateOrCreate(
                ['sku' => $product->slug.'-std'],
                [
                    'platform_product_id' => $product->id,
                    'name' => 'Standard',
                    'label' => 'Standard',
                    'duration_months' => null,
                    'price' => $base,
                    'sort_order' => 0,
                    'is_default' => true,
                    'is_active' => true,
                ]
            );

            return;
        }

        $plans = [
            [1, '1 Month', 1.0],
            [3, '3 Months', 2.7],
            [6, '6 Months', 5.0],
            [12, '1 Year', 9.0],
        ];

        foreach ($plans as $index => [$months, $label, $mult]) {
            PlatformProductVariant::updateOrCreate(
                ['sku' => $product->slug.'-'.$months.'m'],
                [
                    'platform_product_id' => $product->id,
                    'name' => $label,
                    'label' => $label,
                    'duration_months' => $months,
                    'price' => round($base * $mult, 2),
                    'sort_order' => $index,
                    'is_default' => $index === 0,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedGallery(PlatformProduct $product): void
    {
        if ($product->product_type !== PlatformProductType::WebsitePackage) {
            return;
        }

        foreach ([1, 2, 3] as $n) {
            PlatformProductImage::updateOrCreate(
                [
                    'platform_product_id' => $product->id,
                    'path' => '/assets/images/Image_ro410gro410gro41.png',
                    'sort_order' => $n,
                ],
                ['alt' => $product->title.' screenshot '.$n]
            );
        }
    }
}
