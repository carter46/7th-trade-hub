<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\DashboardNavigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CryptoRemovalTest extends TestCase
{
    use RefreshDatabase;

    private const CRYPTO_TOKENS = '/crypto|otc|blockchain|exchange-rate|incoming-deposit/i';

    public function test_public_exchange_url_permanently_redirects_home(): void
    {
        $this->get('/exchange')
            ->assertStatus(301)
            ->assertRedirect(route('home'));
    }

    public function test_dashboard_exchange_urls_redirect_for_members_and_require_login_for_guests(): void
    {
        $this->get('/dashboard/exchange')->assertRedirect(route('login'));
        $this->get('/dashboard/crypto-sell/create')->assertRedirect(route('login'));

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get('/dashboard/exchange')
            ->assertStatus(301)
            ->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->get('/dashboard/crypto-sell/42')
            ->assertStatus(301)
            ->assertRedirect(route('dashboard'));
    }

    public function test_no_live_crypto_routes_remain(): void
    {
        foreach (Route::getRoutes() as $route) {
            $name = (string) $route->getName();
            if (str_contains($name, 'legacy.')) {
                $this->assertSame(['GET', 'HEAD'], $route->methods(), "Legacy route [{$name}] must be GET-only");
                $this->assertInstanceOf(\Closure::class, $route->getAction('uses'), "Legacy route [{$name}] must be a redirect closure");

                continue;
            }

            $action = is_string($route->getAction('uses')) ? $route->getAction('uses') : '';

            $this->assertDoesNotMatchRegularExpression(self::CRYPTO_TOKENS, $route->uri(), "Crypto URI still routed: {$route->uri()}");
            $this->assertDoesNotMatchRegularExpression(self::CRYPTO_TOKENS, $name, "Crypto route name still registered: {$name}");
            $this->assertDoesNotMatchRegularExpression('/Crypto|Otc|Blockchain|ExchangeRate/', $action, "Crypto controller still routed: {$action}");
        }
    }

    public function test_no_crypto_menu_entries_for_members_or_admins(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now()]);
        $member->assignRole('user');

        foreach (['admin' => $admin, 'user' => $member] as $role => $user) {
            foreach (DashboardNavigation::for($role, $user) as $entry) {
                $items = ($entry['type'] ?? 'link') === 'group' ? ($entry['children'] ?? []) : [$entry];
                $items[] = $entry;

                foreach ($items as $item) {
                    $this->assertDoesNotMatchRegularExpression(self::CRYPTO_TOKENS, (string) ($item['label'] ?? ''));
                    $this->assertDoesNotMatchRegularExpression(self::CRYPTO_TOKENS, (string) ($item['route'] ?? ''));
                    $this->assertStringNotContainsStringIgnoringCase('Exchange', (string) ($item['label'] ?? ''));
                }
            }
        }
    }

    public function test_crypto_tables_are_dropped(): void
    {
        foreach ([
            'crypto_sell_requests', 'crypto_deposit_wallets', 'incoming_crypto_transactions',
            'exchange_rates', 'exchange_rate_history', 'otc_pricing_settings', 'wallet_balance_history',
        ] as $table) {
            $this->assertFalse(Schema::hasTable($table), "Table [{$table}] should be dropped");
        }
    }

    public function test_public_pages_contain_no_crypto_copy(): void
    {
        Artisan::call('catalog:backfill-hierarchy');

        foreach ($this->publicPages() as $url) {
            $text = $this->visibleText($this->get($url)->assertOk()->getContent());

            $this->assertDoesNotMatchRegularExpression('/\bcrypto\w*|bitcoin|usdt|\bOTC\b/i', $text, "Crypto copy found on {$url}");
        }
    }

    /** @return list<string> */
    private function publicPages(): array
    {
        return [
            route('home'),
            route('about'),
            route('contact'),
            route('help'),
            route('legal', ['doc' => 'terms']),
            route('legal', ['doc' => 'privacy']),
            route('services'),
        ];
    }

    private function visibleText(string $html): string
    {
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $html) ?? $html;

        return html_entity_decode(strip_tags($html));
    }
}
