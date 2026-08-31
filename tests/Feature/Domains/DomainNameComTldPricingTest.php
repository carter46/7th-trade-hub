<?php

namespace Tests\Feature\Domains;

use App\Models\DomainProvider;
use App\Services\Domains\Providers\NameCom\NameComProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DomainNameComTldPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_tlds_reads_pricing_array_from_namecom_response(): void
    {
        $provider = DomainProvider::query()->where('key', 'namecom')->firstOrFail();
        $provider->update([
            'enabled' => true,
            'sandbox' => true,
            'credentials' => ['username' => 'sandbox-user', 'api_token' => 'token'],
        ]);

        Http::fake([
            'https://api.dev.name.com/core/v1/tldpricing*' => Http::response([
                'lastPage' => 1,
                'nextPage' => null,
                'totalCount' => 2,
                'from' => 1,
                'to' => 2,
                'pricing' => [
                    ['tld' => 'com', 'duration' => 1, 'registrationPrice' => 9.99],
                    ['tld' => 'net', 'duration' => 1, 'registrationPrice' => 11.99],
                ],
            ]),
        ]);

        $tlds = app(NameComProvider::class)->listTlds($provider);

        $this->assertCount(2, $tlds);
        $this->assertSame('com', $tlds[0]->tld);
        $this->assertSame(9.99, $tlds[0]->registrationCost);
        $this->assertSame('net', $tlds[1]->tld);
    }

    public function test_list_tlds_skips_rows_without_registration_price(): void
    {
        $provider = DomainProvider::query()->where('key', 'namecom')->firstOrFail();
        $provider->update([
            'enabled' => true,
            'sandbox' => true,
            'credentials' => ['username' => 'sandbox-user', 'api_token' => 'token'],
        ]);

        Http::fake([
            'https://api.dev.name.com/core/v1/tldpricing*' => Http::response([
                'lastPage' => 1,
                'pricing' => [
                    ['tld' => 'com', 'registrationPrice' => 9.99],
                    ['tld' => 'closed', 'registrationPrice' => null],
                ],
            ]),
        ]);

        $tlds = app(NameComProvider::class)->listTlds($provider);

        $this->assertCount(1, $tlds);
        $this->assertSame('com', $tlds[0]->tld);
    }
}
