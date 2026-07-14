<?php

namespace Tests;

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(RoleSeeder::class);
        }

        if (Schema::hasTable('categories')) {
            $this->seed(\Database\Seeders\CategorySeeder::class);
        }
    }
}
