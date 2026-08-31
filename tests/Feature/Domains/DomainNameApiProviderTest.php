<?php

namespace Tests\Feature\Domains;

use App\Models\DomainProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DomainNameApiProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_domainnameapi_provider_is_seeded(): void
    {
        $this->assertDatabaseHas('domain_providers', [
            'key' => 'domainnameapi',
        ]);
    }

    public function test_domainnameapi_list_tlds_and_search_via_http_fake(): void
    {
        $provider = DomainProvider::query()->where('key', 'domainnameapi')->firstOrFail();
        $provider->update([
            'enabled' => true,
            'sandbox' => true,
            'credentials' => [
                'reseller_id' => '123456',
                'api_key' => 'secret-key',
            ],
        ]);

        Http::fake([
            'https://ote.domainresellerapi.com/api/v1/products/tlds*' => Http::response([
                'items' => [[
                    'name' => 'com',
                    'prices' => [[
                        'register' => [['period' => 1, 'price' => 10.81]],
                    ]],
                ]],
                'totalCount' => 1,
            ]),
            'https://ote.domainresellerapi.com/api/v1/domains/search' => Http::response([
                'success' => true,
                'info' => [
                    'domainName' => 'dna-test.com',
                    'status' => 'available',
                    'price' => 10.81,
                    'currency' => 'USD',
                    'isPremium' => false,
                ],
            ]),
        ]);

        $adapter = app(\App\Services\Domains\Providers\DomainNameApi\DomainNameApiProvider::class);
        $tlds = $adapter->listTlds($provider);
        $this->assertSame('com', $tlds[0]->tld ?? null);

        $availability = $adapter->checkAvailability($provider, 'dna-test.com');
        $this->assertTrue($availability->available);
    }
}
