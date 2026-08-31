<?php

namespace Tests\Feature\Domains;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Models\DomainProvider;
use App\Models\PlatformProduct;
use App\Models\PlatformProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DomainQuoteTest extends TestCase
{
    use RefreshDatabase;

    private function enableNameComProvider(): DomainProvider
    {
        $provider = DomainProvider::query()->where('key', 'namecom')->firstOrFail();
        $provider->update([
            'enabled' => true,
            'is_default' => true,
            'sandbox' => true,
            'credentials' => [
                'username' => 'test-user',
                'api_token' => 'test-token',
            ],
        ]);

        return $provider->fresh();
    }

    private function seedDomainProduct(): PlatformProduct
    {
        $this->seed(\Database\Seeders\PlatformCatalogSeeder::class);

        $product = PlatformProduct::query()->where('slug', 'domain-registration')->first();

        if (! $product) {
            \Illuminate\Support\Facades\Artisan::call('migrate');

            $product = PlatformProduct::query()->where('slug', 'domain-registration')->first();
        }

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

            PlatformProductVariant::query()->create([
                'platform_product_id' => $product->id,
                'name' => 'Standard',
                'slug' => 'domain-registration-std',
                'price' => 0,
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 0,
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

    public function test_domain_quote_returns_token_without_provider_key(): void
    {
        $this->enableNameComProvider();
        $product = $this->seedDomainProduct();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        Http::fake([
            'https://api.dev.name.com/core/v1/domains:checkAvailability' => Http::response([
                'results' => [[
                    'domainName' => 'example.com',
                    'purchasable' => true,
                    'purchaseType' => 'registration',
                    'premium' => false,
                    'purchasePrice' => 12.99,
                ]],
            ]),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.services.domain-quote'), [
                'product_slug' => $product->slug,
                'domain_label' => 'example',
                'domain_tld' => 'com',
            ]);

        $response->assertOk()
            ->assertJsonStructure(['available', 'fqdn', 'retail_price', 'premium', 'quote_token'])
            ->assertJsonMissing(['provider_key', 'provider_cost']);

        $this->assertTrue($response->json('available'));
        $this->assertNotEmpty($response->json('quote_token'));
    }

    public function test_domain_quote_unavailable_when_provider_disabled(): void
    {
        DomainProvider::query()->where('key', 'namecom')->update(['enabled' => false]);

        $product = $this->seedDomainProduct();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.services.domain-quote'), [
                'product_slug' => $product->slug,
                'domain_label' => 'example',
                'domain_tld' => 'com',
            ]);

        $response->assertOk()
            ->assertJson([
                'available' => false,
            ]);
    }

    public function test_markup_formula_applies_ceiling(): void
    {
        $product = $this->seedDomainProduct();
        $policy = app(\App\Services\Domains\PlatformDomainPricingPolicy::class);

        $result = $policy->retailFromProviderCost(12.99, 'USD', $product);

        // ceil(12.99 * 1600 * 1.15) = 23906
        $this->assertSame('23906.00', $result['retail_price']);
    }

    public function test_consume_rejects_price_drift_beyond_tolerance(): void
    {
        $this->enableNameComProvider();
        $product = $this->seedDomainProduct();
        $user = User::factory()->create(['email_verified_at' => now()]);

        Http::fake([
            'https://api.dev.name.com/core/v1/domains:checkAvailability' => Http::sequence()
                ->push([
                    'results' => [[
                        'domainName' => 'drift.com',
                        'purchasable' => true,
                        'purchaseType' => 'registration',
                        'premium' => false,
                        'purchasePrice' => 10.00,
                    ]],
                ])
                ->push([
                    'results' => [[
                        'domainName' => 'drift.com',
                        'purchasable' => true,
                        'purchaseType' => 'registration',
                        'premium' => false,
                        'purchasePrice' => 50.00,
                    ]],
                ]),
        ]);

        $quote = app(\App\Services\Domains\DomainQuoteService::class)->quoteForUser(
            $user,
            $product,
            'drift',
            'com',
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain price changed');

        app(\App\Services\Domains\DomainQuoteService::class)->consumeForPurchase(
            $user,
            $quote['quote_token'],
            'drift.com',
            $product->id,
        );
    }
}
