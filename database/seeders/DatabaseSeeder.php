<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SystemSettingSeeder::class,
            CategorySeeder::class,
            PlatformCategorySeeder::class,
            PlatformCatalogSeeder::class,
            ExchangeRateSeeder::class,
            PlatformWalletSeeder::class,
        ]);

        if (app()->environment('local') || env('SEED_DEMO_DATA', false)) {
            $this->call([
                MarketplaceListingSeeder::class,
                DemoDataSeeder::class,
            ]);
        }
    }
}
