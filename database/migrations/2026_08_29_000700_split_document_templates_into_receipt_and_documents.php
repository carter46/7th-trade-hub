<?php

use App\Models\PlatformProduct;
use App\Models\ProductType;
use App\Models\ServiceCategory;
use App\Support\PlatformCatalogTrim;
use App\Support\SortOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_types') || ! Schema::hasTable('platform_products')) {
            return;
        }

        $categoryId = ServiceCategory::query()->where('slug', 'business-documents')->value('id');

        $receiptService = ProductType::query()->where('slug', 'receipt')->first();
        $legacyService = ProductType::query()->where('slug', 'document_template')->first();

        if ($legacyService && ! $receiptService) {
            $legacyService->forceFill([
                'slug' => 'receipt',
                'name' => 'Receipt',
                'short_description' => config('catalog.types.receipt.short_description'),
                'hero_title' => 'Receipt',
                'hero_subtitle' => config('catalog.types.receipt.short_description'),
                'benefits' => [],
            ])->save();
            $receiptService = $legacyService;
        }

        if ($categoryId && ! ProductType::query()->where('slug', 'document')->exists()) {
            $documentService = new ProductType;
            $documentService->forceFill([
                'slug' => 'document',
                'service_category_id' => $categoryId,
                'name' => 'Documents',
                'sort_order' => (int) ProductType::query()->where('service_category_id', $categoryId)->max('sort_order') + 1,
                'is_active' => true,
                'short_description' => config('catalog.types.document.short_description'),
                'hero_title' => 'Documents',
                'hero_subtitle' => config('catalog.types.document.short_description'),
                'benefits' => [],
                'faq' => config('catalog.types.document.faq', []),
            ])->save();
        }

        DB::table('platform_products')
            ->where('slug', 'invoice-receipt-set')
            ->update(['product_type' => 'receipt']);

        DB::table('platform_products')
            ->where('slug', 'employment-agreement')
            ->update(['product_type' => 'document']);

        DB::table('platform_products')
            ->where('product_type', 'document_template')
            ->whereIn('slug', ['invoice-receipt-set', 'payment-receipt-template', 'sales-receipt-pack'])
            ->update(['product_type' => 'receipt']);

        DB::table('platform_products')
            ->where('product_type', 'document_template')
            ->whereIn('slug', ['employment-agreement', 'nda-bundle', 'sales-contract-pack'])
            ->update(['product_type' => 'document']);

        if (Schema::hasTable('platform_categories')) {
            DB::table('platform_categories')
                ->where('product_type', 'document_template')
                ->update(['product_type' => 'receipt']);
        }

        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PlatformCatalogSeeder', '--force' => true]);
        PlatformCatalogTrim::apply();
        Artisan::call('catalog:backfill-hierarchy');

        if (Schema::hasTable('platform_products')) {
            SortOrder::normalize(
                PlatformProduct::query()
                    ->whereHas('productType.serviceCategory', fn ($q) => $q->system())
            );
        }
    }

    public function down(): void
    {
        // Intentionally not reversed — merged service split is one-way.
    }
};
