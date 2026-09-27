<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_runs_on_a_fully_migrated_database(): void
    {
        $this->assertFalse(Schema::hasTable('platform_categories'));

        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(Role::where('name', 'admin')->exists());
        $this->assertTrue(DB::table('service_categories')->where('slug', 'network-services')->exists());
    }
}
