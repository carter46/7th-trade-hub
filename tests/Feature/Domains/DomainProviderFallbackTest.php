<?php

namespace Tests\Feature\Domains;

use App\Models\DomainProvider;
use App\Models\PlatformProduct;
use App\Models\User;
use App\Services\Domains\DomainQuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DomainProviderFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_fallback_provider_used_when_default_fails(): void
    {
        $default = DomainProvider::query()->where('key', 'namecom')->firstOrFail();
        $default->update([
            'enabled' => true,
            'is_default' => true,
            'sandbox' => true,
            'credentials' => ['username' => 'bad', 'api_token' => 'bad'],
        ]);

        DomainProvider::query()->create([
            'key' => 'namecom-backup',
            'display_name' => 'Name.com Backup',
            'adapter_class' => \App\Services\Domains\Providers\NameCom\NameComProvider::class,
            'enabled' => true,
            'is_default' => false,
            'fallback_priority' => 1,
            'sandbox' => true,
            'capabilities' => ['search', 'availability'],
            'credentials' => ['username' => 'good', 'api_token' => 'good'],
            'health_status' => 'unknown',
        ]);

        $product = PlatformProduct::query()->where('slug', 'domain-registration')->first();
        if (! $product) {
            $product = $this->forceCreatePlatformProduct([
                'title' => 'Domain Registration',
                'slug' => 'domain-registration',
                'product_type' => \App\Enums\PlatformProductType::Domain,
                'status' => \App\Enums\PlatformProductStatus::Published,
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

        Http::fake([
            'https://api.dev.name.com/*' => Http::sequence()
                ->push(['message' => 'Unauthorized'], 401)
                ->push([
                    'results' => [[
                        'domainName' => 'fallback-test.com',
                        'purchasable' => true,
                        'purchaseType' => 'registration',
                        'premium' => false,
                        'purchasePrice' => 10.00,
                    ]],
                ], 200),
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $result = app(DomainQuoteService::class)->quoteForUser(
            $user,
            $product,
            'fallback-test',
            'com',
        );

        $this->assertTrue($result['available']);
        $this->assertSame('namecom-backup', \App\Models\DomainQuote::query()->latest('id')->value('provider_key'));
    }
}
