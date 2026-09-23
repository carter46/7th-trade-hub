<?php

namespace Tests\Feature\Domains;

use App\Models\DomainProvider;
use App\Models\DomainQuote;
use App\Models\Order;
use App\Models\PlatformProduct;
use App\Models\User;
use App\Services\Domains\DomainQuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DomainQuoteSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: PlatformProduct, 2: string, 3: DomainQuote}
     */
    private function seedLockedProviderQuote(): array
    {
        DomainProvider::query()->update(['enabled' => false, 'is_default' => false]);
        DomainProvider::query()->where('key', 'namecom')->update([
            'enabled' => true,
            'is_default' => true,
            'sandbox' => true,
            'credentials' => ['username' => 'u', 'api_token' => 't'],
        ]);

        $this->seed(\Database\Seeders\PlatformCatalogSeeder::class);

        $product = PlatformProduct::query()->where('slug', 'domain-registration')->firstOrFail();
        $product->update([
            'meta' => array_merge($product->meta ?? [], [
                'allowed_tlds' => ['com', 'net', 'online'],
                'domain_markup_percent' => 15,
                'domain_fx_policy' => ['usd_ngn_rate' => 1600],
            ]),
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $plainToken = Str::random(64);
        $quote = DomainQuote::query()->create([
            'user_id' => $user->id,
            'platform_product_id' => $product->id,
            'provider_key' => 'namecom',
            'token_hash' => hash('sha256', $plainToken),
            'fqdn' => 'secure.com',
            'tld' => 'com',
            'sld' => 'secure',
            'provider_cost' => 10,
            'provider_currency' => 'USD',
            'retail_price' => '18400.00',
            'retail_currency' => 'NGN',
            'premium' => false,
            'purchase_type' => 'registration',
            'provider_meta' => [
                'fulfillment' => 'provider',
                'domain_fulfillment' => 'provider',
            ],
            'expires_at' => now()->addMinutes(15),
        ]);

        return [$user, $product->fresh(), $plainToken, $quote];
    }

    public function test_wrong_user_cannot_consume_quote(): void
    {
        [$owner, $product, $token] = $this->seedLockedProviderQuote();
        $other = User::factory()->create(['email_verified_at' => now()]);

        $this->expectException(\InvalidArgumentException::class);

        app(DomainQuoteService::class)->consumeForPurchase($other, $token, 'secure.com', $product->id);
    }

    public function test_wrong_fqdn_rejected(): void
    {
        [$user, $product, $token] = $this->seedLockedProviderQuote();

        $this->expectException(\InvalidArgumentException::class);

        app(DomainQuoteService::class)->consumeForPurchase($user, $token, 'other.com', $product->id);
    }

    public function test_consumed_quote_cannot_be_reused(): void
    {
        [$user, $product, $token, $quote] = $this->seedLockedProviderQuote();

        // Mark consumed without hitting live provider APIs (those are covered elsewhere).
        $quote->update(['consumed_at' => now()]);

        $this->expectException(\InvalidArgumentException::class);

        app(DomainQuoteService::class)->consumeForPurchase($user, $token, 'secure.com', $product->id);
    }

    public function test_disabled_provider_blocks_unreserved_consume(): void
    {
        [$user, $product, $token] = $this->seedLockedProviderQuote();

        DomainProvider::query()->where('key', 'namecom')->update(['enabled' => false]);

        $this->expectException(\InvalidArgumentException::class);

        app(DomainQuoteService::class)->consumeForPurchase($user, $token, 'secure.com', $product->id);
    }

    public function test_reserved_provider_quote_consumes_after_provider_disabled(): void
    {
        [$user, $product, $token, $quote] = $this->seedLockedProviderQuote();

        $order = Order::query()->create([
            'source' => 'platform',
            'user_id' => $user->id,
            'reference' => 'PLT-RSVDIS1',
            'amount' => 100,
            'total_amount' => 100,
            'status' => 'pending',
            'payment_method' => 'gateway',
        ]);

        $quote->update([
            'reserved_at' => now(),
            'reserved_order_id' => $order->id,
            'expires_at' => now()->addHours(3),
        ]);

        DomainProvider::query()->where('key', 'namecom')->update(['enabled' => false]);

        $consumed = app(DomainQuoteService::class)->consumeReservedQuote($user, $quote->fresh(), $order->id);

        $this->assertNotNull($consumed['quote']->consumed_at);
        $this->assertSame('18400.00', $consumed['validated_retail']);
    }
}
