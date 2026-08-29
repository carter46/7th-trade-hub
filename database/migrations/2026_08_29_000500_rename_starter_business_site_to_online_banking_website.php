<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('platform_products')) {
            return;
        }

        $product = DB::table('platform_products')
            ->where('slug', 'starter-business-site')
            ->first();

        if (! $product) {
            return;
        }

        $title = 'Online Banking website';
        $slug = 'online-banking-website';

        DB::table('platform_products')
            ->where('id', $product->id)
            ->update([
                'slug' => $slug,
                'title' => $title,
                'short_description' => "Ready-to-use {$title} from 7th Trade Hub.",
                'description' => "Get started quickly with {$title}. Includes setup guidance, support, and clear deliverables.",
                'demo_url' => 'https://example.com/demo/'.$slug,
                'industry' => 'Finance',
                'updated_at' => now(),
            ]);

        if (Schema::hasTable('platform_product_images')) {
            foreach (DB::table('platform_product_images')
                ->where('platform_product_id', $product->id)
                ->get(['id', 'alt']) as $image) {
                DB::table('platform_product_images')
                    ->where('id', $image->id)
                    ->update([
                        'alt' => str_replace('Starter Business Site', $title, (string) $image->alt),
                        'updated_at' => now(),
                    ]);
            }
        }

        if (Schema::hasTable('platform_product_variants')) {
            $variants = DB::table('platform_product_variants')
                ->where('platform_product_id', $product->id)
                ->get(['id', 'sku']);

            foreach ($variants as $variant) {
                $newSku = str_replace('starter-business-site', $slug, (string) $variant->sku);
                DB::table('platform_product_variants')
                    ->where('id', $variant->id)
                    ->update([
                        'sku' => $newSku,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('platform_products')) {
            return;
        }

        $product = DB::table('platform_products')
            ->where('slug', 'online-banking-website')
            ->first();

        if (! $product) {
            return;
        }

        $title = 'Starter Business Site';
        $slug = 'starter-business-site';

        DB::table('platform_products')
            ->where('id', $product->id)
            ->update([
                'slug' => $slug,
                'title' => $title,
                'short_description' => "Ready-to-use {$title} from 7th Trade Hub.",
                'description' => "Get started quickly with {$title}. Includes setup guidance, support, and clear deliverables.",
                'demo_url' => 'https://example.com/demo/'.$slug,
                'industry' => 'Business',
                'updated_at' => now(),
            ]);
    }
};
