<?php

namespace Database\Seeders;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Models\PlatformCategory;
use App\Models\PlatformProduct;
use App\Models\PlatformProductImage;
use App\Models\PlatformProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PlatformCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            PlatformProductType::Vpn->value => [
                'Dedicated IP VPN',
            ],
            PlatformProductType::Proxy->value => [
                'ISP Proxy Bundle',
            ],
            PlatformProductType::Smtp->value => [
                'Dedicated SMTP IP',
            ],
            PlatformProductType::VirtualPhone->value => [
                'US Virtual Number', 'UK Virtual Number', 'SMS-Ready Number',
            ],
            PlatformProductType::Email->value => [
                'Business Email Starter',
            ],
            PlatformProductType::SocialService->value => [
                'Instagram Growth Pack', 'TikTok Engagement Boost', 'YouTube Views Lite',
                'Twitter Audience Pack', 'LinkedIn Lead Boost', 'Multi-Platform Starter',
            ],
            PlatformProductType::Domain->value => [
                '.com Domain Registration', '.io Domain Registration', '.co Domain Registration',
            ],
            PlatformProductType::EscrowService->value => [
                'Standard Escrow Trade', 'High-Value Escrow', 'Website Sale Escrow',
                'Account Transfer Escrow', 'Milestone Escrow', 'Express Escrow',
            ],
            PlatformProductType::WebsitePackage->value => [
                'Starter Business Site',
            ],
            PlatformProductType::DocumentTemplate->value => [
                'Employment Agreement', 'Invoice & Receipt Set',
            ],
        ];

        $categoryMap = [
            PlatformProductType::WebsitePackage->value => ['wp-starter'],
            PlatformProductType::DocumentTemplate->value => ['dt-hr', 'dt-business'],
            PlatformProductType::Vpn->value => ['vpn-dedicated'],
            PlatformProductType::VirtualPhone->value => ['phone-us', 'phone-uk', 'phone-us'],
            PlatformProductType::Proxy->value => ['proxy-residential'],
            PlatformProductType::Smtp->value => ['smtp-transactional'],
            PlatformProductType::Email->value => ['email-business'],
            PlatformProductType::SocialService->value => ['social-growth', 'social-engagement', 'social-growth', 'social-engagement', 'social-growth', 'social-growth'],
            PlatformProductType::Domain->value => ['domain-registration', 'domain-registration', 'domain-registration'],
            PlatformProductType::EscrowService->value => ['escrow-standard', 'escrow-high-value', 'escrow-high-value', 'escrow-standard', 'escrow-standard', 'escrow-standard'],
        ];

        foreach ($catalog as $type => $titles) {
            foreach ($titles as $i => $title) {
                if ($type === PlatformProductType::EscrowService->value) {
                    continue;
                }

                $slug = Str::slug($title);
                $categoryId = null;
                if (Schema::hasTable('platform_categories') && isset($categoryMap[$type][$i])) {
                    $categoryId = PlatformCategory::where('slug', $categoryMap[$type][$i])->value('id');
                }

                $base = 5000 + ($i * 2500);
                if (in_array($type, [PlatformProductType::WebsitePackage->value, PlatformProductType::Vps->value], true)) {
                    $base = 15000 + ($i * 5000);
                }

                $attrs = [
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
                    'provider' => 'manual',
                    'fulfillment_mode' => 'manual',
                    'auto_renew' => false,
                ];

                if (Schema::hasColumn('platform_products', 'platform_category_id')) {
                    $attrs['platform_category_id'] = $categoryId;
                }

                $product = PlatformProduct::query()->where('slug', $slug)->first();
                if (! $product) {
                    $product = new PlatformProduct;
                    $product->forceFill(array_merge(['slug' => $slug], $attrs))->save();
                }

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
            // Non-destructive: never overwrite admin-edited prices/names on re-seed.
            PlatformProductVariant::firstOrCreate(
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
            PlatformProductVariant::firstOrCreate(
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
