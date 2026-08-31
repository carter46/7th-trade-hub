<?php

namespace Tests\Feature\Domains;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Models\DomainProvider;
use App\Models\PlatformProduct;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class DomainUserToolSkipTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_purchase_does_not_create_user_tool(): void
    {
        config(['domains.default_nameservers' => ['ns1.platform.test', 'ns2.platform.test']]);

        DomainProvider::query()->where('key', 'namecom')->update([
            'enabled' => true,
            'is_default' => true,
            'sandbox' => true,
            'credentials' => ['username' => 'test-user', 'api_token' => 'test-token'],
        ]);

        $product = PlatformProduct::query()->where('slug', 'domain-registration')->first();
        if (! $product) {
            $product = $this->forceCreatePlatformProduct([
                'title' => 'Domain Registration',
                'slug' => 'domain-registration',
                'product_type' => PlatformProductType::Domain,
                'status' => PlatformProductStatus::Published,
                'base_price' => 0,
                'meta' => [
                    'domain_markup_percent' => 15,
                    'domain_fx_policy' => ['usd_ngn_rate' => 1600],
                ],
            ]);
        }

        Http::fake([
            'https://api.dev.name.com/*' => Http::response([
                'results' => [[
                    'domainName' => 'not-a-tool.com',
                    'purchasable' => true,
                    'purchaseType' => 'registration',
                    'premium' => false,
                    'purchasePrice' => 12.99,
                ]],
            ]),
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100000,
            'locked_balance' => 0,
        ]);

        $quote = $this->actingAs($user)
            ->postJson(route('dashboard.services.domain-quote'), [
                'product_slug' => $product->slug,
                'domain_label' => 'not-a-tool',
                'domain_tld' => 'com',
            ]);

        $this->actingAs($user)
            ->post(route('dashboard.services.purchase', $product->slug), [
                'quantity' => 1,
                'domain_quote_token' => $quote->json('quote_token'),
                'domain_fqdn' => 'not-a-tool.com',
                'registrant' => $this->sampleDomainRegistrant(),
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('user_tools', 0);
    }
}
