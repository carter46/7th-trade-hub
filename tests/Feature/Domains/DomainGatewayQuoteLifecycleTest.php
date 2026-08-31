<?php

namespace Tests\Feature\Domains;

use App\Models\DomainProvider;
use App\Models\DomainQuote;
use App\Models\PlatformProduct;
use App\Models\User;
use App\Services\Domains\DomainQuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DomainGatewayQuoteLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_reserved_quote_not_consumed_until_consume_reserved(): void
    {
        DomainProvider::query()->where('key', 'namecom')->update([
            'enabled' => true,
            'is_default' => true,
            'sandbox' => true,
            'credentials' => ['username' => 'u', 'api_token' => 't'],
        ]);

        Http::fake([
            'https://api.dev.name.com/*' => Http::response([
                'results' => [[
                    'domainName' => 'gateway.com',
                    'purchasable' => true,
                    'purchaseType' => 'registration',
                    'premium' => false,
                    'purchasePrice' => 10.00,
                ]],
            ]),
        ]);

        $product = PlatformProduct::query()->where('slug', 'domain-registration')->firstOrFail();
        $product->update([
            'meta' => [
                'domain_markup_percent' => 0,
                'domain_fx_policy' => ['usd_ngn_rate' => 1600],
            ],
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $issued = app(DomainQuoteService::class)->quoteForUser($user, $product, 'gateway', 'com');
        $token = $issued['quote_token'];

        app(DomainQuoteService::class)->previewForCheckout($user, $token, 'gateway.com', $product->id);

        $quote = DomainQuote::query()->latest('id')->firstOrFail();
        $this->assertNull($quote->consumed_at);

        app(DomainQuoteService::class)->reserveForGateway($user, $token, 'gateway.com', 999, $product->id);

        $quote->refresh();
        $this->assertNotNull($quote->reserved_at);
        $this->assertSame(999, (int) $quote->reserved_order_id);
        $this->assertNull($quote->consumed_at);

        app(DomainQuoteService::class)->consumeReservedQuote($user, $quote, 999);

        $quote->refresh();
        $this->assertNotNull($quote->consumed_at);
    }
}
