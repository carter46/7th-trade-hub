<?php

namespace Database\Seeders;

use Database\Seeders\Demo\DemoPlatformSeeder;
use Illuminate\Database\Seeder;

/**
 * @deprecated Use Database\Seeders\Demo\DemoPlatformSeeder via demo:seed / demo:fresh.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DemoPlatformSeeder::class);
    }
}
