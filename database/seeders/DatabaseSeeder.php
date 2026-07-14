<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RoleSeeder::class, CategorySeeder::class]);

        if (app()->environment('local') || env('SEED_DEMO_DATA', false)) {
            $this->call([DemoDataSeeder::class]);
        }
    }
}
