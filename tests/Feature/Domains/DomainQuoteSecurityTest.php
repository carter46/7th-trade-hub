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

class DomainQuoteSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function seedProductAndProvider(): array
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
                    'domainName' => 'secure.com',
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
                'domain_markup_percent' => 15,
                'domain_fx_policy' => ['usd_ngn_rate' => 1600],
            ],
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $quote = app(DomainQuoteService::class)->quoteForUser($user, $product, 'secure', 'com');

        return [$user, $product, $quote['quote_token']];
    }

    public function test_wrong_user_cannot_consume_quote(): void
    {
        [$owner, $product, $token] = $this->seedProductAndProvider();
        $other = User::factory()->create(['email_verified_at' => now()]);

        $this->expectException(\InvalidArgumentException::class);

        app(DomainQuoteService::class)->consumeForPurchase($other, $token, 'secure.com', $product->id);
    }

    public function test_wrong_fqdn_rejected(): void
    {
        [$user, $product, $token] = $this->seedProductAndProvider();

        $this->expectException(\InvalidArgumentException::class);

        app(DomainQuoteService::class)->consumeForPurchase($user, $token, 'other.com', $product->id);
    }

    public function test_consumed_quote_cannot_be_reused(): void
    {
        [$user, $product, $token] = $this->seedProductAndProvider();

        app(DomainQuoteService::class)->consumeForPurchase($user, $token, 'secure.com', $product->id);

        $this->expectException(\InvalidArgumentException::class);

        app(DomainQuoteService::class)->consumeForPurchase($user, $token, 'secure.com', $product->id);
    }

    public function test_disabled_provider_blocks_consume(): void
    {
        [$user, $product, $token] = $this->seedProductAndProvider();

        DomainProvider::query()->where('key', 'namecom')->update(['enabled' => false]);

        $this->expectException(\InvalidArgumentException::class);

        app(DomainQuoteService::class)->consumeForPurchase($user, $token, 'secure.com', $product->id);
    }
}
