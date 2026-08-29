<?php

namespace Tests\Feature\Catalog;

use App\Enums\PlatformProductType;
use App\Models\PlatformProduct;
use App\Support\PlatformCatalogTrim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformCatalogTrimTest extends TestCase
{
    use RefreshDatabase;

    public function test_retires_disallowed_domain_and_website_package_products(): void
    {
        $this->seed(\Database\Seeders\PlatformCatalogSeeder::class);

        foreach ([
            ['slug' => 'ng-domain-registration', 'title' => '.ng Domain Registration', 'type' => PlatformProductType::Domain],
            ['slug' => 'domain-transfer-assist', 'title' => 'Domain Transfer Assist', 'type' => PlatformProductType::Domain],
            ['slug' => 'agency-showcase-site', 'title' => 'Agency Showcase Site', 'type' => PlatformProductType::WebsitePackage],
        ] as $row) {
            $product = new PlatformProduct;
            $product->forceFill([
                'slug' => $row['slug'],
                'title' => $row['title'],
                'product_type' => $row['type'],
                'short_description' => 'Test product',
                'description' => 'Test product',
                'status' => 'published',
                'base_price' => 10000,
                'provider' => 'manual',
                'fulfillment_mode' => 'manual',
            ])->save();
        }

        $this->assertDatabaseHas('platform_products', ['slug' => 'com-domain-registration']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'ng-domain-registration']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'agency-showcase-site']);

        PlatformCatalogTrim::retireDisallowedProducts();

        $this->assertDatabaseHas('platform_products', ['slug' => 'com-domain-registration']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'io-domain-registration']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'co-domain-registration']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'starter-business-site']);

        $this->assertDatabaseMissing('platform_products', ['slug' => 'ng-domain-registration']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'domain-transfer-assist']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'domain-privacy-pack']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'agency-showcase-site']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'restaurant-booking-site']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'law-practice-site']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'clinic-booking-site']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'e-commerce-starter-site']);

        $this->assertSame(3, PlatformProduct::query()->ofType(PlatformProductType::Domain)->count());
        $this->assertSame(1, PlatformProduct::query()->ofType(PlatformProductType::WebsitePackage)->count());
    }
}
