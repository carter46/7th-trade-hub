<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use App\Support\Demo\DemoBatchTracker;
use App\Support\Demo\DemoGate;
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
        $tracker = DemoGate::allowDemoData() ? app(DemoBatchTracker::class) : null;
        if ($tracker && ! $tracker->batch()) {
            $tracker->start('Marketplace vendors '.now()->toDateTimeString(), 'MarketplaceListingSeeder');
        }

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

        $productSlugs = [
            'facebook', 'instagram', 'tiktok', 'twitter', 'linkedin', 'discord',
            'marketplace-vpn', 'marketplace-proxy', 'rdp', 'marketplace-vps', 'marketplace-smtp',
            'websites', 'domains', 'source-code', 'graphics',
        ];

        $products = \App\Models\MarketplaceProduct::query()->whereIn('slug', $productSlugs)->get()->keyBy('slug');
        if ($products->count() < count($productSlugs)) {
            $missing = array_diff($productSlugs, $products->keys()->all());
            throw new RuntimeException(
                'MarketplaceListingSeeder requires MarketplaceProduct rows. Missing: '.implode(', ', $missing)
            );
        }

        $productList = $products->values()->all();
        $walletService = app(WalletProvisioningService::class);

        $listingTitles = [
            'Aged {product} with verified history',
            'Premium {product} package with docs',
            'Ready to use {product} for agencies',
            'Starter {product} bundle with escrow',
            'High trust {product} listing',
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

            $tracker?->track($user);

            $walletService->createWallet($user);
            $wallet = $user->wallet()->first();
            if ($wallet) {
                $tracker?->track($wallet);
            }

            for ($i = 0; $i < 5; $i++) {
                $product = $productList[($vendorIndex * 5 + $i) % count($productList)];
                $title = str_replace('{product}', $product->name, $listingTitles[$i]);
                $slug = Str::slug($vendorData['username'].'-'.$product->slug.'-'.$i);

                $listing = Listing::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'user_id' => $user->id,
                        'marketplace_product_id' => $product->id,
                        'category_id' => $product->category_id,
                        'title' => $title,
                        'description' => "Sold by {$vendorData['name']}. {$product->name} listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.",
                        'price' => 8500 + ($vendorIndex * 1200) + ($i * 750),
                        'category' => $product->slug,
                        'is_active' => true,
                        'status' => 'published',
                        'featured' => $i === 0,
                    ]
                );
                $tracker?->track($listing);
            }
        }
    }
}
