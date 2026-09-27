<?php

namespace Tests\Feature\Catalog;

use App\Enums\PlatformProductType;
use App\Models\PlatformProduct;
use App\Support\PlatformCatalogTrim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IndustryWebsitePackagesMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const NEW_SLUGS = [
        'shipment-and-logistics-website' => 'Shipment and logistics website',
        'investment-broker-website' => 'Investment broker website',
        'real-estate-website' => 'Real estate website',
        'law-firm-website' => 'Law firm website',
        'celebrity-management-website' => 'Celebrity management website',
        'pet-website' => 'Pet website',
    ];

    public function test_duplicates_online_banking_v1_into_six_packages_without_touching_existing_rows(): void
    {
        $this->seed(\Database\Seeders\PlatformCatalogSeeder::class);
        $seededIds = PlatformProduct::query()->whereIn('slug', array_keys(self::NEW_SLUGS))->pluck('id');
        DB::table('platform_product_variants')->whereIn('platform_product_id', $seededIds)->delete();
        DB::table('platform_product_images')->whereIn('platform_product_id', $seededIds)->delete();
        DB::table('platform_products')->whereIn('id', $seededIds)->delete();

        $source = PlatformProduct::query()->where('slug', 'online-banking-v1')->firstOrFail();
        $source->forceFill(['base_price' => 123456, 'status' => 'published'])->save();
        $source->variants()->first()->forceFill(['price' => 99999])->save();
        $before = DB::table('platform_products')->orderBy('id')->get()->map(fn ($r) => (array) $r)->all();
        $variantCount = $source->variants()->count();
        $imageCount = $source->images()->count();

        $this->runMigration();

        $this->assertEquals($before, DB::table('platform_products')->whereNotIn('slug', array_keys(self::NEW_SLUGS))->orderBy('id')->get()->map(fn ($r) => (array) $r)->all());

        foreach (self::NEW_SLUGS as $slug => $title) {
            $product = PlatformProduct::query()->where('slug', $slug)->firstOrFail();
            $this->assertSame($title, $product->title);
            $this->assertSame(PlatformProductType::WebsitePackage, $product->product_type);
            $this->assertSame($source->product_type_id, $product->product_type_id);
            $this->assertSame('published', $product->status->value);
            $this->assertEquals(123456, (float) $product->base_price);
            $this->assertSame($variantCount, $product->variants()->count());
            $this->assertEquals(99999, (float) $product->variants()->orderBy('sort_order')->first()->price);
            $this->assertSame($imageCount, $product->images()->count());
        }
        $this->assertSame('Legal', PlatformProduct::query()->where('slug', 'law-firm-website')->value('industry'));

        $this->runMigration();
        $this->assertSame(9, PlatformProduct::query()->ofType(PlatformProductType::WebsitePackage)->count());

        PlatformCatalogTrim::apply();
        $this->assertSame(9, PlatformProduct::query()->ofType(PlatformProductType::WebsitePackage)->count());
    }

    private function runMigration(): void
    {
        (require database_path('migrations/2026_09_27_000003_add_industry_website_packages.php'))->up();
    }
}
