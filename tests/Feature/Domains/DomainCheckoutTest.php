<?php

namespace Tests\Feature\Domains;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Models\DomainProvider;
use App\Models\DomainQuote;
use App\Models\PlatformProduct;
use App\Models\PlatformProductVariant;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class DomainCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function enableNameComProvider(): void
    {
        DomainProvider::query()->where('key', 'namecom')->update([
            'enabled' => true,
            'is_default' => true,
            'sandbox' => true,
            'credentials' => [
                'username' => 'test-user',
                'api_token' => 'test-token',
            ],
        ]);
    }

    private function seedWebsiteProduct(): PlatformProduct
    {
        \Illuminate\Support\Facades\Artisan::call('catalog:backfill-hierarchy');

        $service = \App\Models\ProductType::query()
            ->where('slug', 'like', '%website%')
            ->first();

        if (! $service) {
            $category = $this->forceCreateServiceCategory([
                'name' => 'Website Services',
                'slug' => 'website-services-test',
                'is_active' => true,
                'sort_order' => 1,
            ]);
            $service = $this->forceCreateProductType([
                'service_category_id' => $category->id,
                'name' => 'Website Package',
                'slug' => 'website-package-test',
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        $product = $this->forceCreatePlatformProduct([
            'title' => 'Online Banking website',
            'slug' => 'online-banking-website-'.Str::lower(Str::random(4)),
            'product_type' => PlatformProductType::WebsitePackage,
            'product_type_id' => $service->id,
            'status' => PlatformProductStatus::Published,
            'base_price' => 10000,
            'sort_order' => 1,
            'provider' => 'manual',
            'fulfillment_mode' => 'manual',
        ]);

        PlatformProductVariant::query()->create([
            'platform_product_id' => $product->id,
            'name' => '3 Months',
            'label' => '3 Months',
            'sku' => $product->slug.'-3m',
            'duration_months' => 3,
            'price' => 27000,
            'sort_order' => 0,
            'is_default' => true,
            'is_active' => true,
        ]);

        return $product->fresh('activeVariants');
    }

    private function seedDomainRegistrationProduct(): PlatformProduct
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

    private function fakeAvailability(string $fqdn = 'example.com', float $price = 12.99): void
    {
        Http::fake([
            'https://api.dev.name.com/core/v1/domains:checkAvailability' => Http::response([
                'results' => [[
                    'domainName' => $fqdn,
                    'purchasable' => true,
                    'purchaseType' => 'registration',
                    'premium' => false,
                    'purchasePrice' => $price,
                ]],
            ]),
        ]);
    }

    public function test_website_checkout_rejects_domain_mode_none(): void
    {
        $product = $this->seedWebsiteProduct();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100000,
            'locked_balance' => 0,
        ]);

        $variant = $product->activeVariants->first();

        $this->actingAs($user)
            ->post(route('dashboard.services.purchase', $product->slug), [
                'variant_id' => $variant->id,
                'quantity' => 1,
                'domain_mode' => 'none',
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_website_checkout_connect_mode_succeeds_without_domain_fee(): void
    {
        $product = $this->seedWebsiteProduct();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100000,
            'locked_balance' => 0,
        ]);

        $variant = $product->activeVariants->first();

        $this->actingAs($user)
            ->post(route('dashboard.services.purchase', $product->slug), [
                'variant_id' => $variant->id,
                'quantity' => 1,
                'domain_mode' => 'connect',
                'domain_label' => 'mysite',
                'domain_tld' => 'com',
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => '27000.00',
        ]);
        $this->assertDatabaseCount('order_items', 1);
    }

    public function test_website_checkout_buy_mode_creates_two_order_lines(): void
    {
        $this->enableNameComProvider();
        $this->fakeAvailability();
        $this->seedDomainRegistrationProduct();

        $product = $this->seedWebsiteProduct();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 200000,
            'locked_balance' => 0,
        ]);

        $variant = $product->activeVariants->first();

        $quoteResponse = $this->actingAs($user)
            ->postJson(route('dashboard.services.domain-quote'), [
                'product_slug' => $product->slug,
                'domain_label' => 'example',
                'domain_tld' => 'com',
            ]);

        $token = $quoteResponse->json('quote_token');

        $this->actingAs($user)
            ->post(route('dashboard.services.purchase', $product->slug), [
                'variant_id' => $variant->id,
                'quantity' => 1,
                'domain_mode' => 'buy',
                'domain_label' => 'example',
                'domain_tld' => 'com',
                'domain_quote_token' => $token,
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('order_items', 2);
        $this->assertDatabaseHas('domain_quotes', [
            'fqdn' => 'example.com',
        ]);
        $this->assertNotNull(DomainQuote::query()->where('fqdn', 'example.com')->value('consumed_at'));
    }

    public function test_domain_product_checkout_redirects_without_quote(): void
    {
        $product = $this->seedDomainRegistrationProduct();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('dashboard.services.checkout', $product->slug))
            ->assertRedirect(route('dashboard.services.product', $product->slug))
            ->assertSessionHas('error');
    }
}
