<?php

namespace App\Console\Commands;

use App\Enums\PlatformProductType;
use App\Models\ProductType;
use App\Models\ServiceCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CatalogBackfillHierarchy extends Command
{
    protected $signature = 'catalog:backfill-hierarchy';

    protected $description = 'Idempotently seed service_categories / product_types and reparent platform_products';

    /** @var array<string, string> enum value → service category slug */
    private const TYPE_TO_CATEGORY = [
        'vpn' => 'network-services',
        'vps' => 'network-services',
        'smtp' => 'network-services',
        'proxy' => 'network-services',
        'email' => 'communication',
        'virtual_phone' => 'communication',
        'social_service' => 'social-media',
        'website_template' => 'website-services',
        'website_package' => 'website-services',
        'domain' => 'website-services',
        'document_template' => 'business-documents',
    ];

    public function handle(): int
    {
        if (! Schema::hasTable('service_categories') || ! Schema::hasTable('product_types')) {
            $this->error('Hierarchy tables missing. Run migrations first.');

            return self::FAILURE;
        }

        $categoriesCreated = $this->seedServiceCategories();
        $servicesCreated = $this->seedProductTypes();
        $productsLinked = $this->linkPlatformProducts();
        $providersSet = $this->setProviderDefaults();

        $this->info("Service categories upserted: {$categoriesCreated}");
        $this->info("Services (product_types) upserted: {$servicesCreated}");
        $this->info("Products linked to services: {$productsLinked}");
        $this->info("Provider defaults applied: {$providersSet}");

        return self::SUCCESS;
    }

    private function seedServiceCategories(): int
    {
        $count = 0;
        $sort = 0;

        foreach (config('catalog.groups', []) as $slug => $group) {
            $mode = ! empty($group['route']) || ($slug === 'trust-escrow')
                ? 'marketplace_link'
                : 'catalog';

            ServiceCategory::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $group['label'] ?? str_replace('-', ' ', ucfirst($slug)),
                    'sort_order' => $sort++,
                    'is_active' => true,
                    'banner_image' => $group['banner_image'] ?? null,
                    'card_image' => $group['card_image'] ?? null,
                    'short_description' => $group['short_description'] ?? null,
                    'hero_title' => $group['hero_title'] ?? ($group['label'] ?? null),
                    'hero_subtitle' => $group['hero_subtitle'] ?? null,
                    'benefits' => $group['benefits'] ?? [],
                    'faq' => $group['faq'] ?? [],
                    'mode' => $mode,
                    'cta_label' => $group['cta'] ?? ($mode === 'marketplace_link' ? 'Open marketplace' : null),
                ]
            );
            $count++;
        }

        return $count;
    }

    private function seedProductTypes(): int
    {
        $count = 0;
        $sortByCategory = [];

        foreach (PlatformProductType::cases() as $case) {
            if ($case === PlatformProductType::EscrowService) {
                continue;
            }

            $slug = $case->value;
            $categorySlug = self::TYPE_TO_CATEGORY[$slug]
                ?? $this->categorySlugFromConfig($slug);

            if (! $categorySlug) {
                $this->warn("No service category mapping for type [{$slug}], skipped.");

                continue;
            }

            $category = ServiceCategory::query()->where('slug', $categorySlug)->first();
            if (! $category) {
                $this->warn("Service category [{$categorySlug}] missing for [{$slug}], skipped.");

                continue;
            }

            $typeConfig = config('catalog.types.'.$slug, []);
            $sortByCategory[$category->id] = ($sortByCategory[$category->id] ?? 0);

            ProductType::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'service_category_id' => $category->id,
                    'name' => $typeConfig['label'] ?? $case->label(),
                    'sort_order' => $sortByCategory[$category->id]++,
                    'is_active' => true,
                    'banner_image' => $typeConfig['banner_image'] ?? null,
                    'card_image' => $typeConfig['card_image'] ?? null,
                    'short_description' => $typeConfig['short_description'] ?? null,
                    'hero_title' => $typeConfig['hero_title'] ?? ($typeConfig['label'] ?? null),
                    'hero_subtitle' => $typeConfig['hero_subtitle'] ?? null,
                    'benefits' => $typeConfig['benefits'] ?? [],
                    'faq' => $typeConfig['faq'] ?? [],
                ]
            );
            $count++;
        }

        return $count;
    }

    private function categorySlugFromConfig(string $typeSlug): ?string
    {
        foreach (config('catalog.groups', []) as $slug => $group) {
            if (in_array($typeSlug, $group['types'] ?? [], true)) {
                return $slug;
            }
        }

        return null;
    }

    private function linkPlatformProducts(): int
    {
        if (! Schema::hasColumn('platform_products', 'product_type_id')) {
            return 0;
        }

        $slugToId = ProductType::query()->pluck('id', 'slug');
        $updated = 0;

        foreach ($slugToId as $slug => $id) {
            $updated += DB::table('platform_products')
                ->where('product_type', $slug)
                ->where(function ($q) use ($id) {
                    $q->whereNull('product_type_id')->orWhere('product_type_id', '!=', $id);
                })
                ->update(['product_type_id' => $id]);
        }

        return $updated;
    }

    private function setProviderDefaults(): int
    {
        if (! Schema::hasColumn('platform_products', 'provider')) {
            return 0;
        }

        return (int) DB::table('platform_products')
            ->whereNull('provider')
            ->update([
                'provider' => 'manual',
                'fulfillment_mode' => 'manual',
                'auto_renew' => false,
            ]);
    }
}
