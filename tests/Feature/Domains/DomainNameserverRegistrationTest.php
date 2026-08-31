<?php

namespace Tests\Feature\Domains;

use App\Models\DomainProvider;
use App\Services\Domains\DomainProviderManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DomainNameserverRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'domains.default_nameservers' => ['ns1.platform.test', 'ns2.platform.test'],
        ]);
    }

    public function test_namecom_registration_payload_includes_platform_nameservers(): void
    {
        DomainProvider::query()->where('key', 'namecom')->update([
            'enabled' => true,
            'sandbox' => true,
            'credentials' => ['username' => 'user', 'api_token' => 'token'],
        ]);

        Http::fake([
            'https://api.dev.name.com/core/v1/domains:checkAvailability' => Http::response([
                'results' => [[
                    'domainName' => 'ns-test.com',
                    'purchasable' => true,
                    'purchaseType' => 'registration',
                    'premium' => false,
                    'purchasePrice' => 10.00,
                ]],
            ]),
            'https://api.dev.name.com/core/v1/domains' => Http::response([
                'domain' => [
                    'domainName' => 'ns-test.com',
                    'nameservers' => ['ns1.platform.test', 'ns2.platform.test'],
                ],
            ]),
            'https://api.dev.name.com/core/v1/domains/ns-test.com' => Http::response([
                'domain' => [
                    'domainName' => 'ns-test.com',
                    'nameservers' => ['ns1.platform.test', 'ns2.platform.test'],
                ],
            ]),
        ]);

        $provider = DomainProvider::query()->where('key', 'namecom')->firstOrFail();
        $adapter = app(DomainProviderManager::class)->adapterFor($provider);

        $result = $adapter->registerDomain($provider, 'ns-test.com', [
            'idempotency_key' => 'test-key',
            'registrant_contact' => $this->sampleDomainRegistrant(),
        ]);

        $this->assertTrue($result->success);

        Http::assertSent(function ($request) {
            if ($request->method() !== 'POST' || ! str_ends_with($request->url(), '/core/v1/domains')) {
                return false;
            }

            $body = json_decode($request->body(), true);

            return ($body['domain']['nameservers'] ?? null) === ['ns1.platform.test', 'ns2.platform.test'];
        });
    }
}
