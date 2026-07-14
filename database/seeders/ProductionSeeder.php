<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RoleSeeder::class, SystemSettingSeeder::class, CategorySeeder::class, PlatformWalletSeeder::class]);
    }
}
