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

class DomainNameComPricingFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_premium_availability_without_price_uses_get_pricing(): void
    {
        DomainProvider::query()->where('key', 'namecom')->update([
            'enabled' => true,
            'is_default' => true,
            'sandbox' => true,
            'credentials' => ['username' => 'user', 'api_token' => 'token'],
        ]);

        Http::fake([
            'https://api.dev.name.com/core/v1/domains:checkAvailability' => Http::response([
                'results' => [[
                    'domainName' => 'noprice.com',
                    'purchasable' => true,
                    'purchaseType' => 'registration',
                    'premium' => false,
                ]],
            ]),
            'https://api.dev.name.com/core/v1/domains/noprice.com:getPricing*' => Http::response([
                'purchasePrice' => 11.50,
            ]),
        ]);

        $product = PlatformProduct::query()->where('slug', 'domain-registration')->first();
        if (! $product) {
            $this->markTestSkipped('domain-registration product not seeded');
        }
        $product->update([
            'meta' => [
                'domain_markup_percent' => 15,
                'domain_fx_policy' => ['usd_ngn_rate' => 1600],
            ],
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $result = app(DomainQuoteService::class)->quoteForUser($user, $product, 'noprice', 'com');

        $this->assertTrue($result['available']);
        Http::assertSent(fn ($request) => str_contains($request->url(), ':getPricing'));
    }
}
