<?php

namespace Tests\Feature\Domains;

use App\Enums\DomainCommerceMode;
use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Models\DomainManualTldPrice;
use App\Models\DomainProvider;
use App\Models\DomainQuote;
use App\Models\DomainRegistration;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PlatformProduct;
use App\Models\PlatformProductVariant;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Domains\DomainCommerceModeResolver;
use App\Services\Domains\DomainRegistrationFulfillmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class DomainManualCommerceTest extends TestCase
{
    use RefreshDatabase;

    private function disableAllProviders(): void
    {
        DomainProvider::query()->update(['enabled' => false, 'is_default' => false]);
        \App\Services\Domains\DomainProviderManager::forgetTldCaches();
    }

    private function seedDomainProduct(): PlatformProduct
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
                    'allowed_tlds' => ['com', 'ng', 'online'],
                ],
            ]);
        } else {
            $product->update([
                'meta' => array_merge($product->meta ?? [], [
                    'allowed_tlds' => ['com', 'ng', 'online'],
                ]),
            ]);
        }

        if (! $product->activeVariants()->exists()) {
            PlatformProductVariant::query()->create([
                'platform_product_id' => $product->id,
                'name' => 'Standard',
                'label' => 'Standard',
                'sku' => $product->slug.'-std',
                'price' => 0,
                'sort_order' => 0,
                'is_default' => true,
                'is_active' => true,
            ]);
        }

        return $product->fresh();
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
                'slug' => 'website-services-manual-test',
                'is_active' => true,
                'sort_order' => 1,
            ]);
            $service = $this->forceCreateProductType([
                'service_category_id' => $category->id,
                'name' => 'Website Package',
                'slug' => 'website-package-manual-test',
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        $product = $this->forceCreatePlatformProduct([
            'title' => 'Manual Mode Website',
            'slug' => 'manual-mode-website-'.Str::lower(Str::random(4)),
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

    private function seedManualPrices(PlatformProduct $product): void
    {
        foreach (['com' => 25000, 'ng' => 18000, 'online' => 15000] as $tld => $price) {
            DomainManualTldPrice::query()->updateOrCreate(
                [
                    'platform_product_id' => $product->id,
                    'tld' => $tld,
                ],
                [
                    'retail_price' => $price,
                    'currency' => 'NGN',
                    'is_active' => true,
                ]
            );
        }
    }

    public function test_commerce_mode_is_manual_when_all_providers_disabled(): void
    {
        $this->disableAllProviders();

        $this->assertTrue(app(DomainCommerceModeResolver::class)->isManual());
        $this->assertSame(DomainCommerceMode::Manual, app(DomainCommerceModeResolver::class)->current());
    }

    public function test_manual_quote_uses_admin_price_without_provider_http(): void
    {
        $this->disableAllProviders();
        $domainProduct = $this->seedDomainProduct();
        $this->seedManualPrices($domainProduct);

        Http::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.services.domain-quote'), [
                'product_slug' => $domainProduct->slug,
                'domain_label' => 'mybusiness',
                'domain_tld' => 'com',
            ])
            ->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonPath('retail_price', '25000.00')
            ->assertJsonPath('fqdn', 'mybusiness.com');

        $this->assertNotEmpty($response->json('quote_token'));
        $this->assertStringContainsString('Availability cannot be verified', (string) $response->json('message'));

        Http::assertNothingSent();

        $this->assertDatabaseHas('domain_quotes', [
            'fqdn' => 'mybusiness.com',
            'provider_key' => DomainQuote::PROVIDER_KEY_MANUAL,
            'retail_price' => '25000.00',
        ]);
    }

    public function test_manual_quote_price_lock_survives_admin_price_change(): void
    {
        $this->disableAllProviders();
        $domainProduct = $this->seedDomainProduct();
        $this->seedManualPrices($domainProduct);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 200000,
            'locked_balance' => 0,
        ]);

        $token = $this->actingAs($user)
            ->postJson(route('dashboard.services.domain-quote'), [
                'product_slug' => $domainProduct->slug,
                'domain_label' => 'lockedprice',
                'domain_tld' => 'com',
            ])
            ->json('quote_token');

        DomainManualTldPrice::query()
            ->where('platform_product_id', $domainProduct->id)
            ->where('tld', 'com')
            ->update(['retail_price' => 99999]);

        $this->actingAs($user)
            ->post(route('dashboard.services.purchase', $domainProduct->slug), [
                'quantity' => 1,
                'domain_quote_token' => $token,
                'domain_fqdn' => 'lockedprice.com',
                'registrant' => $this->sampleDomainRegistrant(),
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->assertDatabaseHas('order_items', [
            'line_total' => '25000.00',
        ]);
        $this->assertDatabaseHas('domain_registrations', [
            'fqdn' => 'lockedprice.com',
            'status' => DomainRegistration::STATUS_PENDING_MANUAL,
            'provider_key' => DomainQuote::PROVIDER_KEY_MANUAL,
        ]);
    }

    public function test_manual_website_buy_adds_separate_domain_line_only(): void
    {
        $this->disableAllProviders();
        $domainProduct = $this->seedDomainProduct();
        $this->seedManualPrices($domainProduct);
        $website = $this->seedWebsiteProduct();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 200000,
            'locked_balance' => 0,
        ]);

        $token = $this->actingAs($user)
            ->postJson(route('dashboard.services.domain-quote'), [
                'product_slug' => $website->slug,
                'domain_label' => 'websitedomain',
                'domain_tld' => 'ng',
            ])
            ->json('quote_token');

        $this->actingAs($user)
            ->post(route('dashboard.services.purchase', $website->slug), [
                'variant_id' => $website->activeVariants->first()->id,
                'quantity' => 1,
                'domain_mode' => 'buy',
                'domain_label' => 'websitedomain',
                'domain_tld' => 'ng',
                'domain_quote_token' => $token,
                'registrant' => $this->sampleDomainRegistrant(),
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->assertDatabaseCount('order_items', 2);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => '45000.00',
        ]);

        $domainLine = OrderItem::query()->where('line_total', '18000.00')->first();
        $this->assertNotNull($domainLine);
        $this->assertSame('manual', $domainLine->options['domain_fulfillment'] ?? null);
        $this->assertSame('buy', $domainLine->options['domain_mode'] ?? null);
    }

    public function test_manual_fulfillment_skips_provider_http(): void
    {
        $this->disableAllProviders();
        Http::fake();

        $user = User::factory()->create();
        $product = $this->seedDomainProduct();

        $order = Order::query()->create([
            'source' => 'platform',
            'user_id' => $user->id,
            'reference' => 'PLT-MANUAL1',
            'amount' => 25000,
            'total_amount' => 25000,
            'status' => 'paid',
            'payment_method' => 'wallet',
        ]);

        $quote = DomainQuote::query()->create([
            'user_id' => $user->id,
            'platform_product_id' => $product->id,
            'provider_key' => DomainQuote::PROVIDER_KEY_MANUAL,
            'token_hash' => hash('sha256', 'manual-token'),
            'fqdn' => 'manualfulfill.com',
            'tld' => 'com',
            'sld' => 'manualfulfill',
            'provider_cost' => 0,
            'provider_currency' => 'NGN',
            'retail_price' => 25000,
            'retail_currency' => 'NGN',
            'premium' => false,
            'purchase_type' => 'registration',
            'provider_meta' => ['fulfillment' => 'manual', 'domain_fulfillment' => 'manual'],
            'expires_at' => now()->addHour(),
            'consumed_at' => now(),
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'item_type' => 'platform_product',
            'item_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 25000,
            'line_total' => 25000,
            'options' => [
                'domain_fqdn' => 'manualfulfill.com',
                'domain_mode' => 'buy',
                'domain_quote_id' => $quote->id,
                'domain_fulfillment' => 'manual',
                'registrant_contact' => $this->sampleDomainRegistrant(),
            ],
        ]);

        app(DomainRegistrationFulfillmentService::class)->fulfillOrder($order->fresh('items'));

        Http::assertNothingSent();
        $this->assertDatabaseHas('domain_registrations', [
            'fqdn' => 'manualfulfill.com',
            'status' => DomainRegistration::STATUS_PENDING_MANUAL,
            'provider_key' => DomainQuote::PROVIDER_KEY_MANUAL,
        ]);
    }

    public function test_website_checkout_shows_buy_connect_in_manual_mode(): void
    {
        $this->disableAllProviders();
        $domainProduct = $this->seedDomainProduct();
        $this->seedManualPrices($domainProduct);
        $website = $this->seedWebsiteProduct();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100000,
            'locked_balance' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.services.checkout', [
                'slug' => $website->slug,
                'variant' => $website->activeVariants->first()->id,
            ]))
            ->assertOk()
            ->assertSee('Buy a new domain', false)
            ->assertSee('Connect existing domain', false)
            ->assertSee('Availability cannot be verified automatically', false);
    }

    public function test_admin_domain_form_shows_manual_prices_only_when_providers_off(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        $domainProduct = $this->seedDomainProduct();

        $this->disableAllProviders();
        $this->actingAs($admin)
            ->get(route('admin.platform-products.edit', $domainProduct))
            ->assertOk()
            ->assertSee('Manual pricing is used only while all domain providers are disabled', false)
            ->assertSee('manual_tld_prices[com]', false);

        DomainProvider::query()->where('key', 'namecom')->update([
            'enabled' => true,
            'is_default' => true,
            'credentials' => ['username' => 'u', 'api_token' => 't'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.platform-products.edit', $domainProduct))
            ->assertOk()
            ->assertDontSee('Manual pricing is used only while all domain providers are disabled', false)
            ->assertDontSee('name="manual_tld_prices[com]"', false);
    }

    public function test_reserved_manual_quote_survives_admin_price_and_tld_deactivation(): void
    {
        $this->disableAllProviders();
        $domainProduct = $this->seedDomainProduct();
        $this->seedManualPrices($domainProduct);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $token = $this->actingAs($user)
            ->postJson(route('dashboard.services.domain-quote'), [
                'product_slug' => $domainProduct->slug,
                'domain_label' => 'reservedhold',
                'domain_tld' => 'com',
            ])
            ->json('quote_token');

        $order = Order::query()->create([
            'source' => 'platform',
            'user_id' => $user->id,
            'reference' => 'PLT-RESERVE1',
            'amount' => 25000,
            'total_amount' => 25000,
            'status' => 'pending',
            'payment_method' => 'gateway',
        ]);

        $reserved = app(\App\Services\Domains\DomainQuoteService::class)->reserveForGateway(
            $user,
            $token,
            'reservedhold.com',
            $order->id,
            $domainProduct->id,
        );

        $this->assertTrue($reserved['quote']->expires_at->greaterThan(now()->addHours(2)));

        DomainManualTldPrice::query()
            ->where('platform_product_id', $domainProduct->id)
            ->where('tld', 'com')
            ->update(['retail_price' => 1, 'is_active' => false]);

        $domainProduct->update([
            'meta' => array_merge($domainProduct->meta ?? [], ['allowed_tlds' => ['ng']]),
        ]);

        $consumed = app(\App\Services\Domains\DomainQuoteService::class)->consumeReservedQuote(
            $user,
            $reserved['quote']->fresh(),
            $order->id,
        );

        $this->assertSame('25000.00', $consumed['validated_retail']);
    }

    public function test_reserved_quote_cannot_be_consumed_by_another_checkout(): void
    {
        $this->disableAllProviders();
        $domainProduct = $this->seedDomainProduct();
        $this->seedManualPrices($domainProduct);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $token = $this->actingAs($user)
            ->postJson(route('dashboard.services.domain-quote'), [
                'product_slug' => $domainProduct->slug,
                'domain_label' => 'reservelock',
                'domain_tld' => 'com',
            ])
            ->json('quote_token');

        $order = Order::query()->create([
            'source' => 'platform',
            'user_id' => $user->id,
            'reference' => 'PLT-RESERVE2',
            'amount' => 25000,
            'total_amount' => 25000,
            'status' => 'pending',
            'payment_method' => 'gateway',
        ]);

        app(\App\Services\Domains\DomainQuoteService::class)->reserveForGateway(
            $user,
            $token,
            'reservelock.com',
            $order->id,
            $domainProduct->id,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('reserved for another checkout');

        app(\App\Services\Domains\DomainQuoteService::class)->consumeForPurchase(
            $user,
            $token,
            'reservelock.com',
            $domainProduct->id,
        );
    }

    public function test_manual_quote_rejects_fqdn_already_pending_on_platform(): void
    {
        $this->disableAllProviders();
        $domainProduct = $this->seedDomainProduct();
        $this->seedManualPrices($domainProduct);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $order = Order::query()->create([
            'source' => 'platform',
            'user_id' => $user->id,
            'reference' => 'PLT-DUP1',
            'amount' => 100,
            'total_amount' => 100,
            'status' => 'paid',
            'payment_method' => 'wallet',
        ]);

        DomainRegistration::query()->create([
            'order_id' => $order->id,
            'fqdn' => 'takenmanual.com',
            'provider_key' => DomainQuote::PROVIDER_KEY_MANUAL,
            'status' => DomainRegistration::STATUS_PENDING_MANUAL,
        ]);

        $this->actingAs($user)
            ->postJson(route('dashboard.services.domain-quote'), [
                'product_slug' => $domainProduct->slug,
                'domain_label' => 'takenmanual',
                'domain_tld' => 'com',
            ])
            ->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonPath('quote_token', null);
    }
}
