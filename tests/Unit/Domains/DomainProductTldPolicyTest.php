<?php

namespace Tests\Unit\Domains;

use App\Enums\PlatformProductType;
use App\Models\PlatformProduct;
use App\Support\Domains\DomainProductTldPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainProductTldPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_featured_tlds_match_config_preset(): void
    {
        $defaults = DomainProductTldPolicy::defaultFeaturedTlds();

        $this->assertContains('com', $defaults);
        $this->assertContains('xyz', $defaults);
        $this->assertNotContains('io', $defaults);
        $this->assertSame(config('domains.default_product_tlds'), $defaults);
    }

    public function test_product_without_meta_uses_default_allowed_set(): void
    {
        $product = $this->makeDomainProduct();

        $this->assertSame(
            DomainProductTldPolicy::defaultFeaturedTlds(),
            DomainProductTldPolicy::allowedTlds($product),
        );
    }

    public function test_featured_and_advanced_split_respects_allowed_list(): void
    {
        $product = $this->makeDomainProduct([
            'allowed_tlds' => ['com', 'io', 'dev'],
        ]);

        $this->assertSame(['com'], DomainProductTldPolicy::featuredTlds($product));
        $this->assertSame(['io', 'dev'], DomainProductTldPolicy::advancedTlds($product));
    }

    public function test_is_allowed_rejects_extensions_outside_product_list(): void
    {
        $product = $this->makeDomainProduct([
            'allowed_tlds' => ['com', 'net'],
        ]);

        $this->assertTrue(DomainProductTldPolicy::isAllowed($product, 'com'));
        $this->assertTrue(DomainProductTldPolicy::isAllowed($product, '.net'));
        $this->assertFalse(DomainProductTldPolicy::isAllowed($product, 'io'));
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function makeDomainProduct(array $meta = []): PlatformProduct
    {
        return $this->forceCreatePlatformProduct([
            'title' => 'Domain Registration',
            'slug' => 'domain-registration-'.uniqid(),
            'product_type' => PlatformProductType::Domain,
            'base_price' => 0,
            'meta' => $meta === [] ? null : $meta,
        ]);
    }
}
