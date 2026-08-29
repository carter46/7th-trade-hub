<?php

namespace Tests\Feature\Catalog;

use App\Enums\PlatformProductType;
use App\Models\PlatformProduct;
use App\Models\ProductType;
use App\Models\ServiceCategory;
use App\Support\PlatformCatalogTrim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
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

        PlatformCatalogTrim::apply();

        $this->assertDatabaseHas('platform_products', ['slug' => 'com-domain-registration']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'io-domain-registration']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'co-domain-registration']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'starter-business-site']);

        $this->assertDatabaseMissing('platform_products', ['slug' => 'ng-domain-registration']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'agency-showcase-site']);

        $this->assertSame(3, PlatformProduct::query()->ofType(PlatformProductType::Domain)->count());
        $this->assertSame(1, PlatformProduct::query()->ofType(PlatformProductType::WebsitePackage)->count());
    }

    public function test_retires_website_templates_vps_and_trims_network_products(): void
    {
        $this->seed(\Database\Seeders\PlatformCatalogSeeder::class);
        Artisan::call('catalog:backfill-hierarchy');

        foreach ([
            ['slug' => 'corporate-landing-kit', 'title' => 'Corporate Landing Kit', 'type' => PlatformProductType::WebsiteTemplate],
            ['slug' => 'starter-vps-1gb', 'title' => 'Starter VPS 1GB', 'type' => PlatformProductType::Vps],
            ['slug' => 'residential-vpn-pro', 'title' => 'Residential VPN Pro', 'type' => PlatformProductType::Vpn],
            ['slug' => 'datacenter-proxy-pack', 'title' => 'Datacenter Proxy Pack', 'type' => PlatformProductType::Proxy],
            ['slug' => 'smtp-starter-10k', 'title' => 'SMTP Starter 10k', 'type' => PlatformProductType::Smtp],
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

        $network = ServiceCategory::query()->where('slug', 'network-services')->firstOrFail();
        $website = ServiceCategory::query()->where('slug', 'website-services')->firstOrFail();

        $retiredTemplate = new ProductType;
        $retiredTemplate->forceFill([
            'slug' => 'website_template',
            'service_category_id' => $website->id,
            'name' => 'Website Templates',
            'sort_order' => 99,
            'is_active' => true,
        ])->save();

        $retiredVps = new ProductType;
        $retiredVps->forceFill([
            'slug' => 'vps',
            'service_category_id' => $network->id,
            'name' => 'VPS',
            'sort_order' => 99,
            'is_active' => true,
        ])->save();

        PlatformCatalogTrim::apply();

        $this->assertDatabaseHas('platform_products', ['slug' => 'dedicated-ip-vpn']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'isp-proxy-bundle']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'dedicated-smtp-ip']);

        $this->assertDatabaseMissing('platform_products', ['slug' => 'corporate-landing-kit']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'starter-vps-1gb']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'residential-vpn-pro']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'datacenter-proxy-pack']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'smtp-starter-10k']);

        $this->assertDatabaseMissing('product_types', ['slug' => 'website_template']);
        $this->assertDatabaseMissing('product_types', ['slug' => 'vps']);

        $this->assertSame(1, PlatformProduct::query()->ofType(PlatformProductType::Vpn)->count());
        $this->assertSame(1, PlatformProduct::query()->ofType(PlatformProductType::Proxy)->count());
        $this->assertSame(1, PlatformProduct::query()->ofType(PlatformProductType::Smtp)->count());
        $this->assertSame(0, PlatformProduct::query()->ofType(PlatformProductType::WebsiteTemplate)->count());
        $this->assertSame(0, PlatformProduct::query()->ofType(PlatformProductType::Vps)->count());
    }

    public function test_retires_disallowed_email_virtual_phone_and_document_products(): void
    {
        $this->seed(\Database\Seeders\PlatformCatalogSeeder::class);

        foreach ([
            ['slug' => 'team-email-5-seats', 'title' => 'Team Email 5 Seats', 'type' => PlatformProductType::Email],
            ['slug' => 'ng-virtual-number', 'title' => 'NG Virtual Number', 'type' => PlatformProductType::VirtualPhone],
            ['slug' => 'sales-contract-pack', 'title' => 'Sales Contract Pack', 'type' => PlatformProductType::DocumentTemplate],
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

        PlatformCatalogTrim::apply();

        $this->assertDatabaseHas('platform_products', ['slug' => 'business-email-starter']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'us-virtual-number']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'uk-virtual-number']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'sms-ready-number']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'employment-agreement']);
        $this->assertDatabaseHas('platform_products', ['slug' => 'invoice-receipt-set']);

        $this->assertDatabaseMissing('platform_products', ['slug' => 'team-email-5-seats']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'custom-domain-email']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'ng-virtual-number']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'business-line-bundle']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'sales-contract-pack']);
        $this->assertDatabaseMissing('platform_products', ['slug' => 'nda-bundle']);

        $this->assertSame(1, PlatformProduct::query()->ofType(PlatformProductType::Email)->count());
        $this->assertSame(3, PlatformProduct::query()->ofType(PlatformProductType::VirtualPhone)->count());
        $this->assertSame(2, PlatformProduct::query()->ofType(PlatformProductType::DocumentTemplate)->count());
    }
}
