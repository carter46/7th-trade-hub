<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MarketplaceProduct;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Social Accounts' => [
                'slug' => 'social-accounts',
                'products' => [
                    ['name' => 'Facebook', 'slug' => 'facebook'],
                    ['name' => 'Twitter / X', 'slug' => 'twitter'],
                    ['name' => 'TikTok', 'slug' => 'tiktok'],
                    ['name' => 'Instagram', 'slug' => 'instagram'],
                    ['name' => 'LinkedIn', 'slug' => 'linkedin'],
                    ['name' => 'Discord', 'slug' => 'discord'],
                ],
            ],
            'Network Services' => [
                'slug' => 'network-services',
                'products' => [
                    ['name' => 'VPN', 'slug' => 'marketplace-vpn'],
                    ['name' => 'Proxy', 'slug' => 'marketplace-proxy'],
                    ['name' => 'RDP', 'slug' => 'rdp'],
                    ['name' => 'VPS', 'slug' => 'marketplace-vps'],
                    ['name' => 'SMTP', 'slug' => 'marketplace-smtp'],
                ],
            ],
            'Digital Goods' => [
                'slug' => 'digital-goods',
                'products' => [
                    ['name' => 'Websites', 'slug' => 'websites'],
                    ['name' => 'Domains', 'slug' => 'domains'],
                    ['name' => 'Source Code', 'slug' => 'source-code'],
                    ['name' => 'Graphics', 'slug' => 'graphics'],
                ],
            ],
        ];

        $sort = 0;
        foreach ($tree as $categoryName => $data) {
            $category = Category::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $categoryName,
                    'type' => 'marketplace',
                    'is_active' => true,
                    'parent_id' => null,
                    'sort_order' => $sort++,
                ]
            );

            $productSort = 0;
            foreach ($data['products'] as $product) {
                MarketplaceProduct::firstOrCreate(
                    ['slug' => $product['slug']],
                    [
                        'name' => $product['name'],
                        'category_id' => $category->id,
                        'is_active' => true,
                        'sort_order' => $productSort++,
                    ]
                );
            }
        }
    }
}
