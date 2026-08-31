<?php

namespace Tests\Feature\Domains;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Models\DomainProvider;
use App\Models\PlatformProduct;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class DomainRegistrantCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_purchase_requires_registrant_contact(): void
    {
        DomainProvider::query()->where('key', 'namecom')->update([
            'enabled' => true,
            'is_default' => true,
            'sandbox' => true,
            'credentials' => ['username' => 'test-user', 'api_token' => 'test-token'],
        ]);

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
        }

        Http::fake([
            'https://api.dev.name.com/core/v1/domains:checkAvailability' => Http::response([
                'results' => [[
                    'domainName' => 'needs-contact.com',
                    'purchasable' => true,
                    'purchaseType' => 'registration',
                    'premium' => false,
                    'purchasePrice' => 12.99,
                ]],
            ]),
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100000,
            'locked_balance' => 0,
        ]);

        $quote = $this->actingAs($user)
            ->postJson(route('dashboard.services.domain-quote'), [
                'product_slug' => $product->slug,
                'domain_label' => 'needs-contact',
                'domain_tld' => 'com',
            ]);

        $this->actingAs($user)
            ->post(route('dashboard.services.purchase', $product->slug), [
                'quantity' => 1,
                'domain_quote_token' => $quote->json('quote_token'),
                'domain_fqdn' => 'needs-contact.com',
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertSessionHasErrors('registrant.first_name');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_domain_purchase_stores_registrant_on_order_line(): void
    {
        DomainProvider::query()->where('key', 'namecom')->update([
            'enabled' => true,
            'is_default' => true,
            'sandbox' => true,
            'credentials' => ['username' => 'test-user', 'api_token' => 'test-token'],
        ]);

        $product = PlatformProduct::query()->where('slug', 'domain-registration')->firstOrFail();
        $product->update([
            'meta' => [
                'domain_markup_percent' => 15,
                'domain_fx_policy' => ['usd_ngn_rate' => 1600],
            ],
        ]);

        Http::fake([
            'https://api.dev.name.com/core/v1/domains:checkAvailability' => Http::response([
                'results' => [[
                    'domainName' => 'stored-contact.com',
                    'purchasable' => true,
                    'purchaseType' => 'registration',
                    'premium' => false,
                    'purchasePrice' => 12.99,
                ]],
            ]),
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100000,
            'locked_balance' => 0,
        ]);

        $registrant = $this->sampleDomainRegistrant();

        $quote = $this->actingAs($user)
            ->postJson(route('dashboard.services.domain-quote'), [
                'product_slug' => $product->slug,
                'domain_label' => 'stored-contact',
                'domain_tld' => 'com',
            ]);

        $this->actingAs($user)
            ->post(route('dashboard.services.purchase', $product->slug), [
                'quantity' => 1,
                'domain_quote_token' => $quote->json('quote_token'),
                'domain_fqdn' => 'stored-contact.com',
                'registrant' => $registrant,
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertRedirect();

        $item = \App\Models\OrderItem::query()->latest('id')->first();
        $this->assertSame($registrant['email'], $item->options['registrant_contact']['email'] ?? null);
    }
}
