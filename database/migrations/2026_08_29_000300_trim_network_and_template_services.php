<?php

use App\Models\PlatformProduct;
use App\Support\PlatformCatalogTrim;
use App\Support\SortOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        PlatformCatalogTrim::apply();

        if (Schema::hasTable('platform_products')) {
            SortOrder::normalize(
                PlatformProduct::query()
                    ->whereHas('productType.serviceCategory', fn ($q) => $q->system())
            );
        }

        if (Schema::hasTable('product_types')) {
            SortOrder::normalize(
                \App\Models\ProductType::query()->whereHas('serviceCategory', fn ($q) => $q->system())
            );
        }
    }

    public function down(): void
    {
        // Retired products and services are intentionally not recreated.
    }
};
