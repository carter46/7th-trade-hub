<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Online Banking catalog:
 * - Current product becomes Online banking v1 (same id / purchases stay linked)
 * - Add Online banking v2 and Online banking v3 as full duplicates
 *
 * Heals earlier mistaken states (keep original name + two copies, or rename-only).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('platform_products')) {
            return;
        }

        $website = DB::table('platform_products')->where('slug', 'online-banking-website')->first();
        $v1 = DB::table('platform_products')->where('slug', 'online-banking-v1')->first();
        $v2 = DB::table('platform_products')->where('slug', 'online-banking-v2')->first();
        $v3 = DB::table('platform_products')->where('slug', 'online-banking-v3')->first();

        // State: original + mistaken v1/v2 copies → shift copies up, rename original to v1.
        if ($website && $v1) {
            if ($v2 && ! $v3) {
                $this->renameProduct((int) $v2->id, 'online-banking-v2', 'online-banking-v3', 'Online banking v3');
                $v2 = null;
                $v3 = DB::table('platform_products')->where('slug', 'online-banking-v3')->first();
            }
            if ($v1 && ! DB::table('platform_products')->where('slug', 'online-banking-v2')->exists()) {
                $this->renameProduct((int) $v1->id, 'online-banking-v1', 'online-banking-v2', 'Online banking v2');
                $v1 = null;
                $v2 = DB::table('platform_products')->where('slug', 'online-banking-v2')->first();
            }
            if (! DB::table('platform_products')->where('slug', 'online-banking-v1')->exists()) {
                $this->renameProduct((int) $website->id, 'online-banking-website', 'online-banking-v1', 'Online banking v1');
            }
        } elseif ($website && ! $v1) {
            $this->renameProduct((int) $website->id, 'online-banking-website', 'online-banking-v1', 'Online banking v1');
        } elseif (! $website && $v1) {
            // Already renamed to v1 earlier — just ensure title.
            if ((string) $v1->title !== 'Online banking v1') {
                DB::table('platform_products')->where('id', $v1->id)->update([
                    'title' => 'Online banking v1',
                    'short_description' => 'Ready-to-use Online banking v1 from 7th Trade Hub.',
                    'updated_at' => now(),
                ]);
            }
        }

        $source = DB::table('platform_products')->where('slug', 'online-banking-v1')->first();
        if (! $source) {
            return;
        }

        $baseSort = (int) ($source->sort_order ?? 0);

        if (! DB::table('platform_products')->where('slug', 'online-banking-v2')->exists()) {
            $this->duplicateProduct((array) $source, 'online-banking-v2', 'Online banking v2', $baseSort + 1);
        } else {
            DB::table('platform_products')->where('slug', 'online-banking-v2')->update([
                'title' => 'Online banking v2',
                'updated_at' => now(),
            ]);
        }

        if (! DB::table('platform_products')->where('slug', 'online-banking-v3')->exists()) {
            $this->duplicateProduct((array) $source, 'online-banking-v3', 'Online banking v3', $baseSort + 2);
        } else {
            DB::table('platform_products')->where('slug', 'online-banking-v3')->update([
                'title' => 'Online banking v3',
                'updated_at' => now(),
            ]);
        }

        // Safety: never leave the legacy slug behind (trim would hard-delete it and cascade user_tools).
        $leftover = DB::table('platform_products')->where('slug', 'online-banking-website')->first();
        if ($leftover) {
            if (! DB::table('platform_products')->where('slug', 'online-banking-v1')->exists()) {
                $this->renameProduct((int) $leftover->id, 'online-banking-website', 'online-banking-v1', 'Online banking v1');
            } elseif (! $this->productHasPurchases((int) $leftover->id) && ! $this->productHasIntegrations((int) $leftover->id)) {
                $this->deleteProductCascade((int) $leftover->id);
            } else {
                throw new \RuntimeException(
                    'online-banking-website still exists alongside online-banking-v1 and has purchases/integrations. Resolve manually before trim.'
                );
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('platform_products')) {
            return;
        }

        foreach (['online-banking-v3', 'online-banking-v2'] as $slug) {
            $row = DB::table('platform_products')->where('slug', $slug)->first();
            if ($row) {
                $this->deleteProductCascade((int) $row->id);
            }
        }

        $v1 = DB::table('platform_products')->where('slug', 'online-banking-v1')->first();
        if ($v1) {
            $this->renameProduct((int) $v1->id, 'online-banking-v1', 'online-banking-website', 'Online Banking website');
        }
    }

    private function productHasPurchases(int $productId): bool
    {
        if (Schema::hasTable('user_tools')
            && DB::table('user_tools')->where('platform_product_id', $productId)->exists()) {
            return true;
        }

        if (Schema::hasTable('order_items')
            && Schema::hasColumn('order_items', 'platform_product_id')
            && DB::table('order_items')->where('platform_product_id', $productId)->exists()) {
            return true;
        }

        return false;
    }

    private function productHasIntegrations(int $productId): bool
    {
        return Schema::hasTable('site_integrations')
            && Schema::hasColumn('site_integrations', 'platform_product_id')
            && DB::table('site_integrations')->where('platform_product_id', $productId)->exists();
    }

    private function renameProduct(int $productId, string $fromSlug, string $toSlug, string $title): void
    {
        if (DB::table('platform_products')->where('slug', $toSlug)->where('id', '!=', $productId)->exists()) {
            throw new RuntimeException("Cannot rename {$fromSlug} → {$toSlug}: target slug already exists.");
        }

        DB::table('platform_products')
            ->where('id', $productId)
            ->update([
                'slug' => $toSlug,
                'title' => $title,
                'short_description' => "Ready-to-use {$title} from 7th Trade Hub.",
                'demo_url' => $this->rewriteDemoUrl(
                    DB::table('platform_products')->where('id', $productId)->value('demo_url'),
                    $fromSlug,
                    $toSlug
                ),
                'updated_at' => now(),
            ]);

        if (Schema::hasTable('platform_product_variants')) {
            foreach (DB::table('platform_product_variants')
                ->where('platform_product_id', $productId)
                ->get(['id', 'sku']) as $variant) {
                $sku = (string) $variant->sku;
                $newSku = str_starts_with($sku, $fromSlug)
                    ? $toSlug.substr($sku, strlen($fromSlug))
                    : str_replace($fromSlug, $toSlug, $sku);

                // Avoid unique collisions during multi-step renames.
                if (DB::table('platform_product_variants')->where('sku', $newSku)->where('id', '!=', $variant->id)->exists()) {
                    $newSku = $toSlug.'-'.$variant->id;
                }

                DB::table('platform_product_variants')
                    ->where('id', $variant->id)
                    ->update([
                        'sku' => $newSku,
                        'updated_at' => now(),
                    ]);
            }
        }

        if (Schema::hasTable('platform_product_images')) {
            foreach (DB::table('platform_product_images')
                ->where('platform_product_id', $productId)
                ->get(['id', 'alt']) as $image) {
                DB::table('platform_product_images')
                    ->where('id', $image->id)
                    ->update([
                        'alt' => str_replace(
                            [
                                'Online Banking website',
                                'Online banking website',
                                'Online banking v1',
                                'Online banking v2',
                                'Online banking v3',
                                'Starter Business Site',
                            ],
                            $title,
                            (string) $image->alt
                        ),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function duplicateProduct(array $source, string $slug, string $title, int $sortOrder): void
    {
        if (DB::table('platform_products')->where('slug', $slug)->exists()) {
            return;
        }

        $row = $source;
        unset($row['id']);
        $row['slug'] = $slug;
        $row['title'] = $title;
        $row['short_description'] = "Ready-to-use {$title} from 7th Trade Hub.";
        $row['sort_order'] = $sortOrder;
        $row['is_featured'] = false;
        $row['demo_url'] = $this->rewriteDemoUrl($source['demo_url'] ?? null, (string) $source['slug'], $slug);
        $row['created_at'] = now();
        $row['updated_at'] = now();

        if (isset($row['description']) && is_string($row['description']) && $row['description'] !== '') {
            $row['description'] = str_replace(
                [(string) $source['title'], 'Online Banking website', 'Starter Business Site'],
                $title,
                $row['description']
            );
        }

        $newId = (int) DB::table('platform_products')->insertGetId($row);

        if (Schema::hasTable('platform_product_variants')) {
            $variants = DB::table('platform_product_variants')
                ->where('platform_product_id', $source['id'])
                ->orderBy('sort_order')
                ->get();

            foreach ($variants as $variant) {
                $data = (array) $variant;
                unset($data['id']);
                $data['platform_product_id'] = $newId;
                $oldSku = (string) ($data['sku'] ?? '');
                $fromSlug = (string) $source['slug'];
                $candidate = str_starts_with($oldSku, $fromSlug)
                    ? $slug.substr($oldSku, strlen($fromSlug))
                    : ($slug.'-'.($data['duration_months'] ?? 'std').(isset($data['duration_months']) ? 'm' : ''));
                if (DB::table('platform_product_variants')->where('sku', $candidate)->exists()) {
                    $candidate = $slug.'-'.uniqid();
                }
                $data['sku'] = $candidate;
                $data['created_at'] = now();
                $data['updated_at'] = now();
                DB::table('platform_product_variants')->insert($data);
            }
        }

        if (Schema::hasTable('platform_product_images')) {
            $images = DB::table('platform_product_images')
                ->where('platform_product_id', $source['id'])
                ->orderBy('sort_order')
                ->get();

            foreach ($images as $image) {
                $data = (array) $image;
                unset($data['id']);
                $data['platform_product_id'] = $newId;
                $data['alt'] = str_replace(
                    [(string) $source['title'], 'Online Banking website', 'Starter Business Site'],
                    $title,
                    (string) ($data['alt'] ?? '')
                );
                $data['created_at'] = now();
                $data['updated_at'] = now();
                DB::table('platform_product_images')->insert($data);
            }
        }
    }

    private function deleteProductCascade(int $productId): void
    {
        if ($this->productHasPurchases($productId) || $this->productHasIntegrations($productId)) {
            throw new \RuntimeException("Refusing to delete platform_product #{$productId}: linked purchases or integrations exist.");
        }

        if (Schema::hasTable('favorites')) {
            DB::table('favorites')
                ->where('favoritable_type', \App\Models\PlatformProduct::class)
                ->where('favoritable_id', $productId)
                ->delete();
        }

        if (Schema::hasTable('platform_product_images')) {
            DB::table('platform_product_images')->where('platform_product_id', $productId)->delete();
        }

        if (Schema::hasTable('platform_product_variants')) {
            DB::table('platform_product_variants')->where('platform_product_id', $productId)->delete();
        }

        DB::table('platform_products')->where('id', $productId)->delete();
    }

    private function rewriteDemoUrl(mixed $demoUrl, string $fromSlug, string $toSlug): mixed
    {
        if (! is_string($demoUrl) || $demoUrl === '') {
            return $demoUrl;
        }

        return str_replace($fromSlug, $toSlug, $demoUrl);
    }
};
