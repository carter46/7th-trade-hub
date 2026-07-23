<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            SystemSettingSeeder::class,
            AnalyticsProviderSeeder::class,
            CategorySeeder::class,
            PlatformCategorySeeder::class,
            PlatformCatalogSeeder::class,
            ExchangeRateSeeder::class,
            PlatformWalletSeeder::class,
        ]);

        // Demo data: local auto, or SEED_DEMO_DATA=true on non-production.
        if (
            ! app()->environment('production')
            && (app()->environment('local') || filter_var(env('SEED_DEMO_DATA', false), FILTER_VALIDATE_BOOLEAN))
        ) {
            $this->call([
                MarketplaceListingSeeder::class,
                \Database\Seeders\Demo\DemoPlatformSeeder::class,
            ]);
        }
    }
}
