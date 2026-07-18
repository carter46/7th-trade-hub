<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class MarketplaceListingSeeder extends Seeder
{
    /**
     * Ten realistic vendor brands × five published listings each.
     * Password for all sample vendors: password
     */
    public function run(): void
    {
        $vendors = [
            ['name' => 'DigitalVault', 'username' => 'digitalvault', 'email' => 'digitalvault@7thtrade.local'],
            ['name' => 'Prime Networks', 'username' => 'primenetworks', 'email' => 'primenetworks@7thtrade.local'],
            ['name' => 'Cloud Edge', 'username' => 'cloudedge', 'email' => 'cloudedge@7thtrade.local'],
            ['name' => 'Secure Connect', 'username' => 'secureconnect', 'email' => 'secureconnect@7thtrade.local'],
            ['name' => 'Pixel Studio', 'username' => 'pixelstudio', 'email' => 'pixelstudio@7thtrade.local'],
            ['name' => 'Code Forge', 'username' => 'codeforge', 'email' => 'codeforge@7thtrade.local'],
            ['name' => 'Atlas Tech', 'username' => 'atlastech', 'email' => 'atlastech@7thtrade.local'],
            ['name' => 'SkyHost', 'username' => 'skyhost', 'email' => 'skyhost@7thtrade.local'],
            ['name' => 'Nexus Digital', 'username' => 'nexusdigital', 'email' => 'nexusdigital@7thtrade.local'],
            ['name' => 'NextGen Media', 'username' => 'nextgenmedia', 'email' => 'nextgenmedia@7thtrade.local'],
        ];

        $leafSlugs = [
            'facebook', 'instagram', 'tiktok', 'twitter', 'linkedin', 'discord',
            'marketplace-vpn', 'marketplace-proxy', 'rdp', 'marketplace-vps', 'marketplace-smtp',
            'websites', 'domains', 'source-code', 'graphics',
        ];

        $leaves = Category::query()->whereIn('slug', $leafSlugs)->get()->keyBy('slug');
        if ($leaves->count() < count($leafSlugs)) {
            $missing = array_diff($leafSlugs, $leaves->keys()->all());
            throw new RuntimeException(
                'MarketplaceListingSeeder requires CategorySeeder leaf categories. Missing: '.implode(', ', $missing)
            );
        }

        $leafList = $leaves->values()->all();
        $walletService = app(WalletProvisioningService::class);

        $listingTitles = [
            'Aged {cat} with verified history',
            'Premium {cat} package with docs',
            'Ready to use {cat} for agencies',
            'Starter {cat} bundle with escrow',
            'High trust {cat} listing',
        ];

        foreach ($vendors as $vendorIndex => $vendorData) {
            $user = User::firstOrCreate(
                ['email' => $vendorData['email']],
                [
                    'name' => $vendorData['name'],
                    'username' => $vendorData['username'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'kyc_level' => 1,
                ]
            );

            if (! $user->hasRole('user')) {
                $user->assignRole('user');
            }

            $walletService->createWallet($user);

            for ($i = 0; $i < 5; $i++) {
                $category = $leafList[($vendorIndex * 5 + $i) % count($leafList)];
                $title = str_replace('{cat}', $category->name, $listingTitles[$i]);
                $slug = Str::slug($vendorData['username'].'-'.$category->slug.'-'.$i);

                Listing::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'user_id' => $user->id,
                        'category_id' => $category->id,
                        'title' => $title,
                        'description' => "Sold by {$vendorData['name']}. {$category->name} listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.",
                        'price' => 8500 + ($vendorIndex * 1200) + ($i * 750),
                        'category' => $category->slug,
                        'is_active' => true,
                        'status' => 'published',
                        'featured' => $i === 0,
                    ]
                );
            }
        }
    }
}
