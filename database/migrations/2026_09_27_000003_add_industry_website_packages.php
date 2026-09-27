<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds six website packages as full duplicates of Online banking v1 (same
 * approach as Online banking v2/v3): price, variants, gallery, category and
 * content are copied, then renamed. Insert-only: existing products, orders and
 * user tools are never changed, and a slug that already exists is skipped.
 */
return new class extends Migration
{
    private const SOURCE_SLUG = 'online-banking-v1';

    /** slug => [title, industry] */
    private const PACKAGES = [
        'shipment-and-logistics-website' => ['Shipment and logistics website', 'Logistics'],
        'investment-broker-website' => ['Investment broker website', 'Finance'],
        'real-estate-website' => ['Real estate website', 'Real Estate'],
        'law-firm-website' => ['Law firm website', 'Legal'],
        'celebrity-management-website' => ['Celebrity management website', 'Entertainment'],
        'pet-website' => ['Pet website', 'Pets'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('platform_products')) {
            return;
        }

        $source = DB::table('platform_products')->where('slug', self::SOURCE_SLUG)->first();
        if (! $source) {
            return;
        }

        $sortOrder = (int) DB::table('platform_products')
            ->where('product_type', $source->product_type)
            ->max('sort_order');

        foreach (self::PACKAGES as $slug => [$title, $industry]) {
            if (DB::table('platform_products')->where('slug', $slug)->exists()) {
                continue;
            }

            $this->duplicateProduct((array) $source, $slug, $title, $industry, ++$sortOrder);
        }
    }

    /** Customers may already own these packages, so rollback leaves them in place; set unwanted ones to draft in the admin. */
    public function down(): void {}

    /**
     * @param  array<string, mixed>  $source
     */
    private function duplicateProduct(array $source, string $slug, string $title, string $industry, int $sortOrder): void
    {
        $sourceTitle = (string) $source['title'];

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

        if (array_key_exists('industry', $row)) {
            $row['industry'] = $industry;
        }

        if (isset($row['description']) && is_string($row['description']) && $row['description'] !== '') {
            $row['description'] = str_replace($sourceTitle, $title, $row['description']);
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
                $data['alt'] = str_replace($sourceTitle, $title, (string) ($data['alt'] ?? ''));
                $data['created_at'] = now();
                $data['updated_at'] = now();
                DB::table('platform_product_images')->insert($data);
            }
        }
    }

    private function rewriteDemoUrl(mixed $demoUrl, string $fromSlug, string $toSlug): mixed
    {
        if (! is_string($demoUrl) || $demoUrl === '') {
            return $demoUrl;
        }

        return str_replace($fromSlug, $toSlug, $demoUrl);
    }
};
