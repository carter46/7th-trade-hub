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
    }

    public function down(): void
    {
        // Retired products are intentionally not recreated.
    }
};
