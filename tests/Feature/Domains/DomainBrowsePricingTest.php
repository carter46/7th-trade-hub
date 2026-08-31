<?php

namespace Tests\Feature\Domains;

use App\Data\Domains\DomainTld;
use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Models\PlatformProduct;
use App\Services\Domains\DomainProviderManager;
use App\Services\Domains\DomainQuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DomainBrowsePricingTest extends TestCase
{
    use RefreshDatabase;

    private function domainProduct(): PlatformProduct
    {
        $product = PlatformProduct::query()->where('slug', 'domain-registration')->first();

        if (! $product) {
            $product = $this->forceCreatePlatformProduct([
                'title' => 'Domain Registration',
                'slug' => 'domain-registration',
                'product_type' => PlatformProductType::Domain,
                'status' => PlatformProductStatus::Published,
                'base_price' => 0,
                'meta' => [
                    'domain_markup_percent' => 15,
                    'domain_fx_policy' => ['usd_ngn_rate' => 1600],
                ],
            ]);
        } else {
            $product->update([
                'meta' => [
                    'domain_markup_percent' => 15,
                    'domain_fx_policy' => ['usd_ngn_rate' => 1600],
                ],
            ]);
        }

        return $product->fresh();
    }

    public function test_display_price_uses_cheapest_cached_tld_retail(): void
    {
        $product = $this->domainProduct();
        Cache::forget('domain.cheapest_retail.'.$product->id);

        $manager = $this->createMock(DomainProviderManager::class);
        $manager->method('mergedTldList')->willReturn([
            new DomainTld(tld: 'com', registrationCost: 12.99, currency: 'USD', purchasable: true),
            new DomainTld(tld: 'io', registrationCost: 39.00, currency: 'USD', purchasable: true),
        ]);

        $this->app->forgetInstance(DomainQuoteService::class);
        $this->app->instance(DomainProviderManager::class, $manager);

        $this->assertSame(23906.0, app(DomainQuoteService::class)->cheapestRetailPrice($product));
        $this->assertSame(23906.0, $product->displayPrice());
    }

    public function test_tld_options_for_ui_uses_merged_provider_list(): void
    {
        $manager = $this->createMock(DomainProviderManager::class);
        $manager->method('mergedTldList')->willReturn([
            new DomainTld(tld: 'com', registrationCost: 10.0, currency: 'USD', purchasable: true),
        ]);

        $this->app->forgetInstance(DomainQuoteService::class);
        $this->app->instance(DomainProviderManager::class, $manager);
        Cache::forget('domain.tlds.merged');

        $options = app(DomainQuoteService::class)->tldOptionsForUi();

        $this->assertSame('com', $options[0]['tld'] ?? null);
    }

    public function test_featured_and_advanced_ui_options_respect_product_allowed_tlds(): void
    {
        $product = $this->domainProduct();
        $product->update([
            'meta' => array_merge($product->meta ?? [], [
                'allowed_tlds' => ['com', 'io', 'dev'],
            ]),
        ]);

        $manager = $this->createMock(DomainProviderManager::class);
        $manager->method('mergedTldList')->willReturn([
            new DomainTld(tld: 'com', registrationCost: 10.0, currency: 'USD', purchasable: true),
            new DomainTld(tld: 'io', registrationCost: 39.0, currency: 'USD', purchasable: true),
            new DomainTld(tld: 'dev', registrationCost: 12.0, currency: 'USD', purchasable: true),
            new DomainTld(tld: 'net', registrationCost: 11.0, currency: 'USD', purchasable: true),
        ]);

        $this->app->forgetInstance(DomainQuoteService::class);
        $this->app->instance(DomainProviderManager::class, $manager);
        Cache::forget('domain.tlds.merged');

        $service = app(DomainQuoteService::class);
        $featured = $service->featuredTldOptionsForUi($product->fresh());
        $advanced = $service->advancedTldOptionsForUi($product->fresh());

        $this->assertSame(['com'], array_column($featured, 'tld'));
        $this->assertSame(['io', 'dev'], array_column($advanced, 'tld'));
    }
}
