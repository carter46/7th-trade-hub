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

class DomainPurchaseTest extends TestCase
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

    public function test_standalone_domain_purchase_charges_quoted_retail(): void
    {
        $this->enableNameComProvider();
        $product = $this->seedDomainProduct();

        Http::fake([
            'https://api.dev.name.com/core/v1/domains:checkAvailability' => Http::response([
                'results' => [[
                    'domainName' => 'purchase-test.com',
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
                'domain_label' => 'purchase-test',
                'domain_tld' => 'com',
            ]);

        $token = $quote->json('quote_token');
        $retail = $quote->json('retail_price');

        $this->actingAs($user)
            ->post(route('dashboard.services.purchase', $product->slug), [
                'quantity' => 1,
                'domain_quote_token' => $token,
                'domain_fqdn' => 'purchase-test.com',
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => $retail,
        ]);
        $this->assertDatabaseCount('order_items', 1);
    }
}
