<?php

namespace Tests;

use App\Models\PlatformProduct;
use App\Models\ProductType;
use App\Models\ServiceCategory;
use Database\Seeders\PermissionSeeder;
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

        if (Schema::hasTable('permissions')) {
            $this->seed(PermissionSeeder::class);
        }

        if (Schema::hasTable('integration_providers')) {
            $this->seed(\Database\Seeders\CommunicationsSeeder::class);
        }

        if (Schema::hasTable('categories')) {
            $this->seed(\Database\Seeders\CategorySeeder::class);
        }
    }

    /** @param  array<string, mixed>  $attrs */
    protected function forceCreateServiceCategory(array $attrs): ServiceCategory
    {
        $category = new ServiceCategory;
        $category->forceFill($attrs)->save();

        return $category->fresh();
    }

    /** @param  array<string, mixed>  $attrs */
    protected function forceCreateProductType(array $attrs): ProductType
    {
        $service = new ProductType;
        $service->forceFill($attrs)->save();

        return $service->fresh();
    }

    /** @param  array<string, mixed>  $attrs */
    protected function forceCreatePlatformProduct(array $attrs): PlatformProduct
    {
        $product = new PlatformProduct;
        $product->forceFill($attrs)->save();

        return $product->fresh();
    }
}
