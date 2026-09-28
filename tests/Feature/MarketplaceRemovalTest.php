<?php

namespace Tests\Feature;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Models\Order;
use App\Models\PlatformProduct;
use App\Models\PlatformProductVariant;
use App\Models\ProductType;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Support\DashboardNavigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarketplaceRemovalTest extends TestCase
{
    use RefreshDatabase;

    private const MARKETPLACE_TOKENS = '/escrow|marketplace|watchlist|dispute|\blistings?\b|\bsales\b|\bmessages\b|checkout\.store$/i';

    private const REMOVED_ADMIN_PATHS = [
        '/admin/escrows', '/admin/listings', '/admin/listings/5', '/admin/marketplace-categories',
        '/admin/marketplace-products', '/admin/crypto-sells', '/admin/blockchain-monitoring',
    ];

    public function test_public_marketplace_urls_permanently_redirect_to_services(): void
    {
        $this->get('/marketplace')->assertStatus(301)->assertRedirect('/services');
        $this->get('/marketplace/some-old-listing')->assertStatus(301)->assertRedirect('/services');
    }

    public function test_dashboard_marketplace_urls_redirect_for_members_and_require_login_for_guests(): void
    {
        foreach (['/dashboard/marketplace', '/dashboard/orders', '/dashboard/messages', '/dashboard/listings'] as $path) {
            $this->get($path)->assertRedirect(route('login'));
        }

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)->get('/dashboard/marketplace')->assertStatus(301)->assertRedirect(route('dashboard'));
        $this->actingAs($user)->get('/dashboard/messages/9')->assertStatus(301)->assertRedirect(route('dashboard'));
        $this->actingAs($user)->get('/dashboard/listings/create')->assertStatus(301)->assertRedirect(route('dashboard'));
        $this->actingAs($user)->get('/dashboard/orders')->assertStatus(301)->assertRedirect(route('dashboard.service-orders'));
    }

    public function test_removed_admin_paths_redirect_only_for_admins(): void
    {
        foreach (self::REMOVED_ADMIN_PATHS as $path) {
            $this->get($path)->assertRedirect(route('login'));
        }

        $member = User::factory()->create(['email_verified_at' => now()]);
        $member->assignRole('user');
        foreach (self::REMOVED_ADMIN_PATHS as $path) {
            $this->actingAs($member)->get($path)->assertForbidden();
        }

        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        foreach (self::REMOVED_ADMIN_PATHS as $path) {
            $this->actingAs($admin)->get($path)->assertStatus(301)->assertRedirect(route('admin'));
        }

        foreach (['listings', 'escrows'] as $tab) {
            $this->actingAs($admin)
                ->get("/admin/users/{$member->id}/{$tab}")
                ->assertStatus(301)
                ->assertRedirect(route('admin.users.show', $member));
        }

        $this->actingAs($admin)->post('/admin/escrows/1/release')->assertStatus(405);
    }

    public function test_no_live_escrow_listing_or_marketplace_routes_remain(): void
    {
        foreach (Route::getRoutes() as $route) {
            $name = (string) $route->getName();
            if (str_contains($name, 'legacy.')) {
                continue;
            }

            $uri = str_replace(['website-listings', 'website-listing'], '', $route->uri());
            $cleanName = str_replace(['website-listings', 'website-listing'], '', $name);
            $action = is_string($route->getAction('uses')) ? $route->getAction('uses') : '';

            $this->assertDoesNotMatchRegularExpression(self::MARKETPLACE_TOKENS, $uri, "Marketplace URI still routed: {$route->uri()}");
            $this->assertDoesNotMatchRegularExpression(self::MARKETPLACE_TOKENS, $cleanName, "Marketplace route name still registered: {$name}");
            $this->assertDoesNotMatchRegularExpression(
                '/Escrow|Marketplace|Watchlist|MessageController|Listing/',
                str_replace('WebsiteListingController', '', $action),
                "Marketplace controller still routed: {$action}"
            );
        }
    }

    public function test_no_marketplace_menu_entries(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now()]);
        $member->assignRole('user');

        foreach (['admin' => $admin, 'user' => $member] as $role => $user) {
            foreach (DashboardNavigation::for($role, $user) as $entry) {
                $items = ($entry['type'] ?? 'link') === 'group' ? ($entry['children'] ?? []) : [];
                $items[] = $entry;

                foreach ($items as $item) {
                    $label = (string) ($item['label'] ?? '');
                    $this->assertDoesNotMatchRegularExpression('/escrow|marketplace|listing|watchlist|sales|messages/i', $label);
                }
            }
        }
    }

    public function test_marketplace_tables_are_dropped_and_kept_columns_lose_only_their_foreign_keys(): void
    {
        foreach (['escrows', 'listings', 'listing_versions', 'messages', 'reviews', 'watchlists', 'marketplace_products', 'categories'] as $table) {
            $this->assertFalse(Schema::hasTable($table), "Table [{$table}] should be dropped");
        }

        foreach (['orders' => 'listing_id', 'transactions' => 'escrow_id'] as $table => $column) {
            $this->assertTrue(Schema::hasColumn($table, $column), "{$table}.{$column} keeps its historical values");
            $this->assertSame([], array_values(array_filter(
                Schema::getForeignKeys($table),
                fn ($fk) => $fk['columns'] === [$column]
            )));
        }
    }

    public function test_public_pages_have_no_escrow_copy_or_trust_card(): void
    {
        Artisan::call('catalog:backfill-hierarchy');

        foreach ([
            route('home'), route('about'), route('contact'), route('help'),
            route('legal', ['doc' => 'terms']), route('legal', ['doc' => 'privacy']), route('services'),
        ] as $url) {
            $text = $this->visibleText($this->get($url)->assertOk()->getContent());

            $this->assertStringNotContainsStringIgnoringCase('escrow', $text, "Escrow copy found on {$url}");
            $this->assertStringNotContainsStringIgnoringCase('Trust & Escrow', $text);
        }
    }

    public function test_platform_wallet_checkout_still_works_and_orders_page_has_no_escrow_column(): void
    {
        $product = $this->seedVpnProduct();

        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 20000,
            'locked_balance' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('dashboard.services.purchase', $product->slug), [
                'variant_id' => $product->activeVariants->first()->id,
                'quantity' => 1,
                'payment_method' => 'wallet',
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $order = Order::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('platform', $order->source);
        $this->assertSame('wallet', $order->payment_method);
        $this->assertEqualsWithDelta(15000.0, (float) $wallet->fresh()->balance, 0.01);
        $this->assertSame(0, Transaction::query()
            ->whereIn('type', ['escrow_lock', 'escrow_release', 'platform_fee', 'listing_hold', 'listing_hold_release'])
            ->count());

        $html = $this->actingAs($user)
            ->get(route('dashboard.service-orders'))
            ->assertOk()
            ->assertSee($order->reference)
            ->getContent();
        $this->assertStringNotContainsStringIgnoringCase('escrow', $this->visibleText($html));
    }

    public function test_website_listings_still_work_and_stay_in_sitemap(): void
    {
        Artisan::call('catalog:backfill-hierarchy');
        $service = ProductType::query()->where('slug', 'website_package')->firstOrFail();
        $product = $this->forceCreatePlatformProduct([
            'product_type_id' => $service->id,
            'product_type' => PlatformProductType::WebsitePackage,
            'title' => 'Corporate Starter Site',
            'slug' => 'corporate-starter-site',
            'short_description' => 'Hosted website package',
            'status' => PlatformProductStatus::Published,
            'base_price' => 30000,
            'sort_order' => 1,
            'provider' => 'manual',
            'fulfillment_mode' => 'manual',
        ]);

        $this->get(route('website-listings'))->assertOk()->assertSee('Corporate Starter Site');
        $canonical = route('services.nested.show', [
            'category' => 'website-services',
            'service' => 'website_package',
            'productSlug' => $product->slug,
        ]);
        $this->get(route('website-listings.show', $product->slug))->assertRedirect($canonical);
        $this->get($canonical)->assertOk()->assertSee('Corporate Starter Site');

        Cache::forget('sitemap.xml.v2');
        $this->get(route('sitemap'))->assertOk()->assertSee(route('website-listings'), false);
    }

    private function seedVpnProduct(): PlatformProduct
    {
        Artisan::call('catalog:backfill-hierarchy');
        $service = ProductType::query()->where('slug', 'vpn')->firstOrFail();

        $product = $this->forceCreatePlatformProduct([
            'product_type_id' => $service->id,
            'product_type' => PlatformProductType::Vpn,
            'title' => 'Removal Test VPN',
            'slug' => 'removal-test-vpn',
            'short_description' => 'Test VPN',
            'status' => PlatformProductStatus::Published,
            'base_price' => 5000,
            'sort_order' => 1,
            'provider' => 'manual',
            'fulfillment_mode' => 'manual',
            'auto_renew' => false,
        ]);

        PlatformProductVariant::query()->create([
            'platform_product_id' => $product->id,
            'name' => '1 Month',
            'price' => 5000,
            'duration_months' => 1,
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return $product->fresh('activeVariants');
    }

    private function visibleText(string $html): string
    {
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $html) ?? $html;

        return html_entity_decode(strip_tags($html));
    }
}
