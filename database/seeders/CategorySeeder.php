<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Social Accounts' => [
                'slug' => 'social-accounts',
                'children' => [
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
                'children' => [
                    ['name' => 'VPN', 'slug' => 'marketplace-vpn'],
                    ['name' => 'Proxy', 'slug' => 'marketplace-proxy'],
                    ['name' => 'RDP', 'slug' => 'rdp'],
                    ['name' => 'VPS', 'slug' => 'marketplace-vps'],
                    ['name' => 'SMTP', 'slug' => 'marketplace-smtp'],
                ],
            ],
            'Digital Goods' => [
                'slug' => 'digital-goods',
                'children' => [
                    ['name' => 'Websites', 'slug' => 'websites'],
                    ['name' => 'Domains', 'slug' => 'domains'],
                    ['name' => 'Source Code', 'slug' => 'source-code'],
                    ['name' => 'Graphics', 'slug' => 'graphics'],
                ],
            ],
        ];

        $sort = 0;
        foreach ($tree as $parentName => $data) {
            $parent = Category::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $parentName,
                    'type' => 'marketplace',
                    'is_active' => true,
                    'parent_id' => null,
                    'sort_order' => $sort++,
                ]
            );

            $childSort = 0;
            foreach ($data['children'] as $child) {
                Category::firstOrCreate(
                    ['slug' => $child['slug']],
                    [
                        'name' => $child['name'],
                        'type' => 'marketplace',
                        'is_active' => true,
                        'parent_id' => $parent->id,
                        'sort_order' => $childSort++,
                    ]
                );
            }
        }
    }
}
