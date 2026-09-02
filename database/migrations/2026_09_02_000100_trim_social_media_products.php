<?php

use App\Enums\PlatformProductStatus;
use App\Models\PlatformCategory;
use App\Models\PlatformProduct;
use App\Models\PlatformProductVariant;
use App\Models\ProductType;
use App\Support\PlatformCatalogTrim;
use App\Support\SortOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureFacebookProduct();

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

    private function ensureFacebookProduct(): void
    {
        if (! Schema::hasTable('platform_products')) {
            return;
        }

        if (PlatformProduct::query()->where('slug', 'facebook-growth-pack')->exists()) {
            return;
        }

        $title = 'Facebook Growth Pack';
        $categoryId = null;

        if (Schema::hasTable('platform_categories')) {
            $categoryId = PlatformCategory::query()
                ->where('slug', 'social-growth')
                ->value('id');
        }

        $productTypeId = Schema::hasTable('product_types')
            ? ProductType::query()->where('slug', 'social_service')->value('id')
            : null;

        $attrs = [
            'slug' => 'facebook-growth-pack',
            'product_type' => 'social_service',
            'title' => $title,
            'short_description' => "Ready-to-use {$title} from 7th Trade Hub.",
            'description' => "Get started quickly with {$title}. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.",
            'status' => PlatformProductStatus::Published,
            'is_featured' => false,
            'sort_order' => 4,
            'hero_image' => null,
            'demo_url' => null,
            'demo_username' => null,
            'demo_password' => null,
            'industry' => null,
            'framework' => null,
            'is_responsive' => true,
            'is_seo_ready' => false,
            'support_period' => null,
            'features' => ['Fast setup', 'NGN wallet checkout', 'Email support'],
            'requirements' => ['Active 7th Trade Hub account', 'Funded wallet for purchase'],
            'whats_included' => ['Product access', 'Basic setup guide', 'Support window'],
            'faqs' => [
                ['q' => 'How fast is delivery?', 'a' => 'Most digital products are available right after payment.'],
                ['q' => 'Can I get a refund?', 'a' => 'Refunds follow our support policy for unused digital goods.'],
            ],
            'support_text' => 'Open a support ticket from your dashboard if you need help.',
            'base_price' => 15000,
            'meta' => null,
            'provider' => 'manual',
            'fulfillment_mode' => 'manual',
            'auto_renew' => false,
        ];

        if (Schema::hasColumn('platform_products', 'platform_category_id')) {
            $attrs['platform_category_id'] = $categoryId;
        }

        if (Schema::hasColumn('platform_products', 'product_type_id') && $productTypeId) {
            $attrs['product_type_id'] = $productTypeId;
        }

        $product = new PlatformProduct;
        $product->forceFill($attrs)->save();

        if (Schema::hasTable('platform_product_variants')) {
            PlatformProductVariant::firstOrCreate(
                ['sku' => $product->slug.'-std'],
                [
                    'platform_product_id' => $product->id,
                    'name' => 'Standard',
                    'label' => 'Standard',
                    'duration_months' => null,
                    'price' => $product->base_price,
                    'sort_order' => 0,
                    'is_default' => true,
                    'is_active' => true,
                ]
            );
        }
    }
};
