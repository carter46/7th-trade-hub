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

        // Demo data when ALLOW_DEMO_DATA / SEED_DEMO_DATA is true (works with APP_ENV=production for pre-launch).
        if (\App\Support\Demo\DemoGate::allowDemoData()) {
            $this->call([
                MarketplaceListingSeeder::class,
                \Database\Seeders\Demo\DemoPlatformSeeder::class,
            ]);
        }
    }
}
