<?php

namespace Tests\Feature\Domains;

use App\Models\DomainProvider;
use App\Models\PlatformProduct;
use App\Models\User;
use App\Services\Domains\DomainProviderManager;
use App\Services\Domains\DomainQuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DomainTldRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tld_only_on_fallback_provider_quotes_via_fallback(): void
    {
        $default = DomainProvider::query()->where('key', 'namecom')->firstOrFail();
        $default->update([
            'enabled' => true,
            'is_default' => true,
            'sandbox' => true,
            'credentials' => ['username' => 'default', 'api_token' => 'token'],
        ]);

        DomainProvider::query()->create([
            'key' => 'dna-test',
            'display_name' => 'DNA Test',
            'adapter_class' => \App\Services\Domains\Providers\DomainNameApi\DomainNameApiProvider::class,
            'enabled' => true,
            'is_default' => false,
            'fallback_priority' => 1,
            'sandbox' => true,
            'capabilities' => [],
            'credentials' => ['reseller_id' => 'r1', 'api_key' => 'k1'],
            'health_status' => 'unknown',
        ]);

        Http::fake([
            'https://api.dev.name.com/core/v1/tldpricing*' => Http::response(['tlds' => [
                ['tld' => 'com', 'registrationPrice' => 12.99],
            ]]),
            'https://ote.domainresellerapi.com/api/v1/products/tlds*' => Http::response([
                'items' => [
                    ['name' => 'xyz', 'prices' => [['register' => [['period' => 1, 'price' => 9.99]]]]],
                ],
            ]),
            'https://ote.domainresellerapi.com/api/v1/domains/search' => Http::response([
                'success' => true,
                'info' => [
                    'domainName' => 'foo.xyz',
                    'status' => 'available',
                    'price' => 9.99,
                    'currency' => 'USD',
                ],
            ]),
        ]);

        $registry = app(DomainProviderManager::class)->mergedTldList();
        $tlds = collect($registry)->pluck('tld')->all();
        $this->assertContains('xyz', $tlds);

        $product = PlatformProduct::query()->where('slug', 'domain-registration')->first();
        if (! $product) {
            $this->markTestSkipped('domain-registration product not seeded');
        }

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $result = app(DomainQuoteService::class)->quoteForUser($user, $product, 'foo', 'xyz');

        $this->assertTrue($result['available']);
        $this->assertSame('dna-test', \App\Models\DomainQuote::query()->latest('id')->value('provider_key'));
    }

    public function test_domain_unavailable_on_primary_does_not_call_fallback(): void
    {
        $default = DomainProvider::query()->where('key', 'namecom')->firstOrFail();
        $default->update([
            'enabled' => true,
            'is_default' => true,
            'sandbox' => true,
            'credentials' => ['username' => 'default', 'api_token' => 'token'],
        ]);

        DomainProvider::query()->create([
            'key' => 'dna-test-2',
            'display_name' => 'DNA Test 2',
            'adapter_class' => \App\Services\Domains\Providers\DomainNameApi\DomainNameApiProvider::class,
            'enabled' => true,
            'is_default' => false,
            'fallback_priority' => 1,
            'sandbox' => true,
            'capabilities' => [],
            'credentials' => ['reseller_id' => 'r1', 'api_key' => 'k1'],
            'health_status' => 'unknown',
        ]);

        Http::fake([
            'https://api.dev.name.com/core/v1/tldpricing*' => Http::response(['tlds' => [
                ['tld' => 'xyz', 'registrationPrice' => 12.99],
            ]]),
            'https://ote.domainresellerapi.com/api/v1/products/tlds*' => Http::response([
                'items' => [
                    ['name' => 'xyz', 'prices' => [['register' => [['period' => 1, 'price' => 9.99]]]]],
                ],
            ]),
            'https://api.dev.name.com/core/v1/domains:checkAvailability' => Http::response([
                'results' => [[
                    'domainName' => 'taken.xyz',
                    'purchasable' => false,
                    'purchaseType' => 'registration',
                    'premium' => false,
                ]],
            ]),
            'https://ote.domainresellerapi.com/api/v1/domains/search' => Http::response([
                'success' => true,
                'info' => [
                    'domainName' => 'taken.xyz',
                    'status' => 'available',
                    'price' => 9.99,
                ],
            ]),
        ]);

        $product = PlatformProduct::query()->where('slug', 'domain-registration')->first();
        if (! $product) {
            $this->markTestSkipped('domain-registration product not seeded');
        }

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $result = app(DomainQuoteService::class)->quoteForUser($user, $product, 'taken', 'xyz');

        $this->assertFalse($result['available']);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'domainresellerapi.com/api/v1/domains/search'));
    }
}
