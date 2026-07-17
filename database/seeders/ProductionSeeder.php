<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
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

        if (filter_var(env('SEED_DEMO_DATA', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->call([
                MarketplaceListingSeeder::class,
            ]);
        }
    }
}
