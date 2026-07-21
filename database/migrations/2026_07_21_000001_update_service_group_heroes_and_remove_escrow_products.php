<?php

use App\Models\PlatformProduct;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('catalog_page_contents')) {
            $now = now();
            $groups = [
                'network-services' => [
                    'banner_image' => 'assets/images/Network Services_1.jpg',
                    'card_image' => 'assets/images/Network Services_1.jpg',
                ],
                'communication' => [
                    'banner_image' => 'assets/images/Communication_1.jpg',
                    'card_image' => 'assets/images/Communication_1.jpg',
                ],
                'social-media' => [
                    'banner_image' => 'assets/images/Social_Media.jpg',
                    'card_image' => 'assets/images/Social_Media.jpg',
                ],
                'website-services' => [
                    'banner_image' => 'assets/images/Website_Services.jpg',
                    'card_image' => 'assets/images/Website_Services.jpg',
                ],
                'business-documents' => [
                    'hero_title' => 'Documents & Receipts',
                    'banner_image' => 'assets/images/Business_Documents.jpg',
                    'card_image' => 'assets/images/Business_Documents.jpg',
                ],
                'trust-escrow' => [
                    'hero_title' => 'Trust & Escrow',
                    'hero_subtitle' => 'Explore escrow-protected purchases in the marketplace.',
                    'short_description' => 'Buy and sell digital products with marketplace escrow protection.',
                    'banner_image' => 'assets/images/flat-lay-real-estate-concept.jpg',
                    'card_image' => 'assets/images/flat-lay-real-estate-concept.jpg',
                ],
            ];

            foreach ($groups as $key => $values) {
                DB::table('catalog_page_contents')->updateOrInsert(
                    ['scope' => 'group', 'key' => $key],
                    array_merge($values, ['updated_at' => $now, 'created_at' => $now])
                );
            }

            DB::table('catalog_page_contents')
                ->where('scope', 'type')
                ->where('key', 'escrow_service')
                ->delete();
        }

        if (Schema::hasTable('platform_products')) {
            $productIds = DB::table('platform_products')
                ->where('product_type', 'escrow_service')
                ->pluck('id');

            if ($productIds->isNotEmpty() && Schema::hasTable('favorites')) {
                DB::table('favorites')
                    ->where('favoritable_type', PlatformProduct::class)
                    ->whereIn('favoritable_id', $productIds)
                    ->delete();
            }

            DB::table('platform_products')
                ->where('product_type', 'escrow_service')
                ->delete();
        }

        if (Schema::hasTable('platform_categories')) {
            DB::table('platform_categories')
                ->where('product_type', 'escrow_service')
                ->delete();
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('catalog_page_contents')) {
            return;
        }

        DB::table('catalog_page_contents')
            ->where('scope', 'group')
            ->whereIn('key', [
                'network-services',
                'communication',
                'social-media',
                'website-services',
                'business-documents',
                'trust-escrow',
            ])
            ->update([
                'banner_image' => null,
                'card_image' => null,
                'updated_at' => now(),
            ]);

        DB::table('catalog_page_contents')
            ->where('scope', 'group')
            ->where('key', 'business-documents')
            ->update([
                'hero_title' => 'Business Documents',
                'updated_at' => now(),
            ]);

        // Deleted escrow products/categories are intentionally not recreated:
        // their complete product, variant, and media data cannot be reconstructed safely.
    }
};
