<?php

namespace Tests\Feature\Marketplace;

use App\Models\Category;
use App\Models\Listing;
use App\Models\MarketplaceProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceHierarchyAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_marketplace_categories_index_shows_roots_only(): void
    {
        $root = Category::query()->create([
            'name' => 'Network Services',
            'slug' => 'network-services-test',
            'type' => 'marketplace',
            'is_active' => true,
            'parent_id' => null,
            'sort_order' => 0,
        ]);
        Category::query()->create([
            'name' => 'Legacy Child',
            'slug' => 'legacy-child-vpn',
            'type' => 'marketplace',
            'is_active' => true,
            'parent_id' => $root->id,
            'sort_order' => 0,
        ]);
        MarketplaceProduct::query()->create([
            'category_id' => $root->id,
            'name' => 'VPN',
            'slug' => 'test-vpn-product',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.marketplace-categories'))
            ->assertOk()
            ->assertSee('Network Services')
            ->assertDontSee('Legacy Child');
    }

    public function test_marketplace_product_requires_root_category(): void
    {
        $root = Category::query()->create([
            'name' => 'Digital Goods',
            'slug' => 'digital-goods-test',
            'type' => 'marketplace',
            'is_active' => true,
            'parent_id' => null,
            'sort_order' => 0,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.marketplace-products.store'), [
                'name' => 'Custom Domain Pack',
                'slug' => 'custom-domain-pack-unique',
                'category_id' => $root->id,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.marketplace-products'));

        $this->assertDatabaseHas('marketplace_products', [
            'name' => 'Custom Domain Pack',
            'category_id' => $root->id,
            'slug' => 'custom-domain-pack-unique',
        ]);
    }

    public function test_category_create_never_accepts_parent(): void
    {
        $root = Category::query()->create([
            'name' => 'Social',
            'slug' => 'social-test',
            'type' => 'marketplace',
            'is_active' => true,
            'parent_id' => null,
            'sort_order' => 0,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.marketplace-categories.store'), [
                'name' => 'Should Be Root',
                'parent_id' => $root->id,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.marketplace-categories'));

        $created = Category::query()->where('name', 'Should Be Root')->first();
        $this->assertNotNull($created);
        $this->assertNull($created->parent_id);
    }

    public function test_listings_index_tabs_and_product_filter(): void
    {
        $admin = $this->admin();
        $seller = User::factory()->create(['email_verified_at' => now()]);
        $seller->assignRole('user');

        $root = Category::query()->create([
            'name' => 'Net',
            'slug' => 'net-filter',
            'type' => 'marketplace',
            'is_active' => true,
            'parent_id' => null,
            'sort_order' => 0,
        ]);
        $product = MarketplaceProduct::query()->create([
            'category_id' => $root->id,
            'name' => 'VPN',
            'slug' => 'vpn-filter',
            'is_active' => true,
            'sort_order' => 0,
        ]);
        Listing::query()->create([
            'user_id' => $seller->id,
            'category_id' => $root->id,
            'marketplace_product_id' => $product->id,
            'title' => 'NordVPN Account',
            'slug' => 'nordvpn-account-test',
            'description' => 'Test',
            'price' => 5000,
            'category' => $product->slug,
            'is_active' => true,
            'status' => 'published',
            'featured' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.listings', ['status' => 'active', 'product' => $product->id]))
            ->assertOk()
            ->assertSee('NordVPN Account');
    }
}
