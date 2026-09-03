<?php

namespace Tests\Feature\Admin;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Enums\UserToolStatus;
use App\Models\Order;
use App\Models\PlatformProductVariant;
use App\Models\User;
use App\Models\UserTool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminManualPurchaseTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->admin()->create(['email_verified_at' => now()]);
    }

    private function memberUser(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        return $user;
    }

    private function seedVpnProduct(): \App\Models\PlatformProduct
    {
        $product = $this->forceCreatePlatformProduct([
            'title' => 'Test Hosting',
            'slug' => 'test-hosting-'.Str::lower(Str::random(4)),
            'product_type' => PlatformProductType::Vpn,
            'status' => PlatformProductStatus::Published,
            'base_price' => 2500,
            'sort_order' => 1,
            'provider' => 'manual',
            'fulfillment_mode' => 'manual',
        ]);

        PlatformProductVariant::query()->create([
            'platform_product_id' => $product->id,
            'name' => 'Monthly',
            'label' => 'Monthly',
            'sku' => $product->slug.'-m',
            'duration_months' => 1,
            'price' => 2500,
            'sort_order' => 0,
            'is_default' => true,
            'is_active' => true,
        ]);

        return $product->fresh('activeVariants');
    }

    public function test_manual_purchase_catalog_returns_products_and_variants(): void
    {
        $product = $this->seedVpnProduct();

        $this->actingAs($this->adminUser())
            ->getJson(route('admin.users.manual-purchase.catalog'))
            ->assertOk()
            ->assertJsonFragment(['slug' => $product->slug])
            ->assertJsonStructure([
                'categories',
                'services',
                'products' => [
                    ['id', 'title', 'slug', 'variants' => [['id', 'label', 'price']]],
                ],
            ]);
    }

    public function test_admin_can_manual_purchase_for_user_from_users_route(): void
    {
        $admin = $this->adminUser();
        $member = $this->memberUser();
        $product = $this->seedVpnProduct();
        $variant = $product->activeVariants->first();

        $this->actingAs($admin)
            ->post(route('admin.users.manual-purchase', $member), [
                'product_slug' => $product->slug,
                'variant_id' => $variant->id,
                'mark_paid' => '1',
            ])
            ->assertRedirect(route('admin.users.tools', $member))
            ->assertSessionHas('status');

        $order = Order::query()->where('user_id', $member->id)->first();
        $this->assertNotNull($order);
        $this->assertSame('paid', $order->status);
    }

    public function test_website_manual_purchase_requires_existing_domain(): void
    {
        \Illuminate\Support\Facades\Artisan::call('catalog:backfill-hierarchy');

        $service = \App\Models\ProductType::query()
            ->where('slug', 'like', '%website%')
            ->orWhere('name', 'like', '%Website Package%')
            ->first();

        if (! $service) {
            $category = $this->forceCreateServiceCategory([
                'name' => 'Website Services',
                'slug' => 'website-services-admin-test',
                'is_active' => true,
                'sort_order' => 1,
            ]);
            $service = $this->forceCreateProductType([
                'service_category_id' => $category->id,
                'name' => 'Website Package',
                'slug' => 'website-package-admin-test',
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        $product = $this->forceCreatePlatformProduct([
            'title' => 'Banking Site',
            'slug' => 'banking-site-'.Str::lower(Str::random(4)),
            'product_type' => PlatformProductType::WebsitePackage,
            'product_type_id' => $service->id,
            'status' => PlatformProductStatus::Published,
            'base_price' => 10000,
            'sort_order' => 1,
            'provider' => 'manual',
            'fulfillment_mode' => 'manual',
        ]);

        $variant = PlatformProductVariant::query()->create([
            'platform_product_id' => $product->id,
            'name' => '3 Months',
            'label' => '3 Months',
            'sku' => $product->slug.'-3m',
            'duration_months' => 3,
            'price' => 27000,
            'sort_order' => 0,
            'is_default' => true,
            'is_active' => true,
        ]);

        $admin = $this->adminUser();
        $member = $this->memberUser();

        $this->actingAs($admin)
            ->post(route('admin.users.manual-purchase', $member), [
                'product_slug' => $product->slug,
                'variant_id' => $variant->id,
                'mark_paid' => '1',
                'domain_fqdn' => 'shop.customer-example.com',
            ])
            ->assertRedirect(route('admin.users.tools', $member))
            ->assertSessionHas('status');

        $tool = UserTool::query()->where('user_id', $member->id)->first();
        $this->assertNotNull($tool);
        $this->assertSame(UserToolStatus::PendingSetup, $tool->status);
        $this->assertSame('https://shop.customer-example.com', $tool->site_url);
        $this->assertSame('https://shop.customer-example.com', $tool->suggestedSiteUrl());

        $order = Order::query()->where('user_id', $member->id)->with('items')->first();
        $this->assertNotNull($order);
        $this->assertSame(
            'shop.customer-example.com',
            $order->items->first()?->options['domain_fqdn'] ?? null
        );
    }

    public function test_admin_can_adjust_tool_expiry(): void
    {
        $admin = $this->adminUser();
        $member = $this->memberUser();

        $tool = UserTool::query()->create([
            'user_id' => $member->id,
            'platform_product_id' => $this->seedVpnProduct()->id,
            'status' => UserToolStatus::Active,
            'purchased_at' => now()->subMonth(),
            'configured_at' => now()->subMonth(),
            'expires_at' => now()->addMonth(),
            'duration_months' => 3,
            'instance_sequence' => 1,
        ]);

        $newExpiry = now()->addMonths(6)->format('Y-m-d');

        $this->actingAs($admin)
            ->post(route('admin.users.tools.expiry', [$member, $tool]), [
                'expires_at' => $newExpiry,
            ])
            ->assertRedirect(route('admin.users.tools.show', [$member, $tool]))
            ->assertSessionHas('status');

        $this->assertSame(
            $newExpiry,
            $tool->fresh()->expires_at?->format('Y-m-d')
        );
    }

    public function test_admin_can_save_livechat_logins_and_user_can_copy_password(): void
    {
        $admin = $this->adminUser();
        $member = $this->memberUser();
        $product = $this->seedVpnProduct();

        $tool = UserTool::query()->create([
            'user_id' => $member->id,
            'platform_product_id' => $product->id,
            'platform_product_variant_id' => $product->activeVariants->first()->id,
            'status' => UserToolStatus::Active,
            'purchased_at' => now()->subDay(),
            'configured_at' => now()->subDay(),
            'expires_at' => now()->addMonths(3),
            'duration_months' => 3,
            'site_url' => 'https://customer.example.com',
            'admin_email' => 'owner@example.com',
            'admin_password' => 'SitePass123!',
            'instance_sequence' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.tools.livechat', [$member, $tool]), [
                'livechat_name' => 'Jivo Support',
                'livechat_url' => 'https://app.jivochat.com/login',
                'livechat_email' => 'chat@example.com',
                'livechat_password' => 'ChatPass123!',
            ])
            ->assertRedirect(route('admin.users.tools.show', [$member, $tool]))
            ->assertSessionHas('status');

        $tool->refresh();
        $this->assertSame('Jivo Support', $tool->livechat_name);
        $this->assertSame('https://app.jivochat.com/login', $tool->livechat_url);
        $this->assertSame('chat@example.com', $tool->livechat_email);
        $this->assertSame('ChatPass123!', $tool->livechat_password);

        $this->actingAs($member)
            ->get(route('dashboard.my-tools.show', $tool))
            ->assertOk()
            ->assertSee('Livechat logins')
            ->assertSee('Jivo Support')
            ->assertSee('chat@example.com');

        $this->actingAs($member)
            ->postJson(route('dashboard.my-tools.livechat-password', $tool))
            ->assertOk()
            ->assertJson(['password' => 'ChatPass123!']);
    }

    public function test_product_tutorial_shows_on_product_page_and_my_tools(): void
    {
        $member = $this->memberUser();
        $product = $this->seedVpnProduct();
        $product->forceFill([
            'tutorial_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'tutorial_description' => 'How to set up your banking site admin panel.',
        ])->save();

        $this->actingAs($member)
            ->get(route('dashboard.services.product', $product->slug))
            ->assertOk()
            ->assertSee('Watch tutorial')
            ->assertSee('How to set up your banking site admin panel.');

        $tool = UserTool::query()->create([
            'user_id' => $member->id,
            'platform_product_id' => $product->id,
            'status' => UserToolStatus::Active,
            'purchased_at' => now()->subDay(),
            'configured_at' => now()->subDay(),
            'expires_at' => now()->addMonths(3),
            'site_url' => 'https://customer.example.com',
            'instance_sequence' => 1,
        ]);

        $this->actingAs($member)
            ->get(route('dashboard.my-tools.show', $tool))
            ->assertOk()
            ->assertSee('Tutorials')
            ->assertSee('How to set up your banking site admin panel.')
            ->assertSee('Watch tutorial');
    }
}
