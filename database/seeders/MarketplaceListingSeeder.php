<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MarketplaceListingSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::firstOrCreate(
            ['email' => 'seller@7thtrade.local'],
            [
                'name' => 'Demo Seller',
                'username' => 'demoseller',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'kyc_level' => 1,
            ]
        );

        if (! $seller->hasRole('user')) {
            $seller->assignRole('user');
        }

        app(WalletProvisioningService::class)->createWallet($seller);

        $leafSlugs = [
            'facebook', 'instagram', 'tiktok', 'twitter', 'discord',
            'marketplace-vpn', 'marketplace-proxy', 'marketplace-vps', 'marketplace-smtp', 'rdp',
            'websites', 'domains', 'source-code', 'graphics', 'linkedin',
        ];

        foreach ($leafSlugs as $index => $slug) {
            $category = Category::where('slug', $slug)->first();
            if (! $category) {
                continue;
            }

            $title = $category->name.' Listing '.($index + 1);
            Listing::updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'user_id' => $seller->id,
                    'category_id' => $category->id,
                    'title' => $title,
                    'description' => "Demo marketplace listing for {$category->name}. Escrow-protected. Admin/demo seller can edit or delete.",
                    'price' => 10000 + ($index * 1500),
                    'category' => $category->slug,
                    'is_active' => true,
                    'status' => 'published',
                    'featured' => $index < 3,
                ]
            );
        }
    }
}
