<?php

namespace Tests\Feature\Domains;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Models\DomainProvider;
use App\Models\DomainQuote;
use App\Models\DomainRegistration;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PlatformProduct;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Domains\DomainRegistrationFulfillmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DomainRegistrationFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_fulfillment_registers_domain_after_paid_order(): void
    {
        DomainProvider::query()->where('key', 'namecom')->update([
            'enabled' => true,
            'sandbox' => true,
            'credentials' => ['username' => 'user', 'api_token' => 'token'],
        ]);

        Http::fake([
            'https://api.dev.name.com/core/v1/domains:checkAvailability' => Http::response([
                'results' => [[
                    'domainName' => 'fulfill.com',
                    'purchasable' => true,
                    'purchaseType' => 'registration',
                    'premium' => false,
                    'purchasePrice' => 12.99,
                ]],
            ]),
            'https://api.dev.name.com/core/v1/domains' => Http::response([
                'domain' => ['domainName' => 'fulfill.com'],
            ]),
        ]);

        $user = User::factory()->create();
        $order = Order::query()->create([
            'source' => 'platform',
            'user_id' => $user->id,
            'reference' => 'PLT-TEST01',
            'amount' => 100,
            'total_amount' => 100,
            'status' => 'paid',
            'payment_method' => 'wallet',
        ]);

        $product = PlatformProduct::query()->where('slug', 'domain-registration')->first();
        if (! $product) {
            $product = PlatformProduct::query()->create([
                'title' => 'Domain Registration',
                'slug' => 'domain-registration',
                'product_type' => PlatformProductType::Domain,
                'status' => PlatformProductStatus::Published,
                'base_price' => 0,
            ]);
        }

        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'item_type' => 'platform_product',
            'item_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100,
            'line_total' => 100,
            'options' => [
                'domain_fqdn' => 'fulfill.com',
                'domain_mode' => 'buy',
                'domain_quote_id' => DomainQuote::query()->create([
                    'user_id' => $user->id,
                    'platform_product_id' => $product->id,
                    'provider_key' => 'namecom',
                    'token_hash' => hash('sha256', 'test-token'),
                    'fqdn' => 'fulfill.com',
                    'tld' => 'com',
                    'sld' => 'fulfill',
                    'provider_cost' => 12.99,
                    'provider_currency' => 'USD',
                    'retail_price' => 23906,
                    'retail_currency' => 'NGN',
                    'premium' => false,
                    'purchase_type' => 'registration',
                    'expires_at' => now()->addMinutes(15),
                    'consumed_at' => now(),
                ])->id,
                'premium' => false,
                'registrant_contact' => $this->sampleDomainRegistrant(),
            ],
        ]);

        app(DomainRegistrationFulfillmentService::class)->fulfillOrder($order->fresh('items'));

        $this->assertDatabaseHas('domain_registrations', [
            'order_item_id' => $item->id,
            'fqdn' => 'fulfill.com',
            'status' => DomainRegistration::STATUS_REGISTERED,
        ]);
    }
}
