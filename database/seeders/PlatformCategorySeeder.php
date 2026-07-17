<?php

namespace Database\Seeders;

use App\Enums\PlatformProductType;
use App\Models\PlatformCategory;
use Illuminate\Database\Seeder;

class PlatformCategorySeeder extends Seeder
{
    public function run(): void
    {
        $trees = [
            PlatformProductType::WebsiteTemplate->value => [
                ['name' => 'Corporate', 'slug' => 'wt-corporate'],
                ['name' => 'Agency', 'slug' => 'wt-agency'],
                ['name' => 'Law', 'slug' => 'wt-law'],
                ['name' => 'Restaurant', 'slug' => 'wt-restaurant'],
                ['name' => 'Medical', 'slug' => 'wt-medical'],
            ],
            PlatformProductType::WebsitePackage->value => [
                ['name' => 'Starter Sites', 'slug' => 'wp-starter'],
                ['name' => 'Business Sites', 'slug' => 'wp-business'],
                ['name' => 'E-commerce Sites', 'slug' => 'wp-ecommerce'],
            ],
            PlatformProductType::DocumentTemplate->value => [
                ['name' => 'Legal', 'slug' => 'dt-legal'],
                ['name' => 'Business', 'slug' => 'dt-business'],
                ['name' => 'Personal', 'slug' => 'dt-personal'],
                ['name' => 'HR', 'slug' => 'dt-hr'],
            ],
            PlatformProductType::Vpn->value => [
                ['name' => 'Residential', 'slug' => 'vpn-residential'],
                ['name' => 'Business', 'slug' => 'vpn-business'],
                ['name' => 'Gaming', 'slug' => 'vpn-gaming'],
                ['name' => 'Dedicated', 'slug' => 'vpn-dedicated'],
            ],
            PlatformProductType::VirtualPhone->value => [
                ['name' => 'US Numbers', 'slug' => 'phone-us'],
                ['name' => 'UK Numbers', 'slug' => 'phone-uk'],
                ['name' => 'NG Numbers', 'slug' => 'phone-ng'],
            ],
            PlatformProductType::Vps->value => [
                ['name' => 'Shared VPS', 'slug' => 'vps-shared'],
                ['name' => 'Dedicated VPS', 'slug' => 'vps-dedicated'],
            ],
            PlatformProductType::Proxy->value => [
                ['name' => 'Datacenter', 'slug' => 'proxy-datacenter'],
                ['name' => 'Residential', 'slug' => 'proxy-residential'],
                ['name' => 'Mobile', 'slug' => 'proxy-mobile'],
            ],
            PlatformProductType::Smtp->value => [
                ['name' => 'Transactional', 'slug' => 'smtp-transactional'],
                ['name' => 'Marketing', 'slug' => 'smtp-marketing'],
            ],
            PlatformProductType::Email->value => [
                ['name' => 'Business Mail', 'slug' => 'email-business'],
                ['name' => 'Team Mail', 'slug' => 'email-team'],
            ],
            PlatformProductType::SocialService->value => [
                ['name' => 'Growth', 'slug' => 'social-growth'],
                ['name' => 'Engagement', 'slug' => 'social-engagement'],
            ],
            PlatformProductType::Domain->value => [
                ['name' => 'Registration', 'slug' => 'domain-registration'],
                ['name' => 'Transfer', 'slug' => 'domain-transfer'],
            ],
            PlatformProductType::EscrowService->value => [
                ['name' => 'Standard', 'slug' => 'escrow-standard'],
                ['name' => 'High Value', 'slug' => 'escrow-high-value'],
            ],
        ];

        foreach ($trees as $type => $children) {
            $sort = 0;
            foreach ($children as $child) {
                PlatformCategory::firstOrCreate(
                    ['slug' => $child['slug']],
                    [
                        'name' => $child['name'],
                        'product_type' => $type,
                        'parent_id' => null,
                        'sort_order' => $sort++,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
