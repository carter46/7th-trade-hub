<?php

namespace Tests\Feature\Marketplace;

use App\Models\Category;
use App\Models\Listing;
use App\Models\MarketplaceProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceBrowseRoutesTest extends TestCase
{
    use RefreshDatabase;

    private function seedHierarchy(): array
    {
        $category = Category::query()->create([
            'name' => 'Digital Goods',
            'slug' => 'digital-goods-browse',
            'type' => 'marketplace',
            'is_active' => true,
            'parent_id' => null,
            'sort_order' => 0,
            'hero_title' => 'Digital Goods Hub',
            'seo_title' => 'Buy Digital Goods',
            'seo_description' => 'Browse digital goods with escrow protection.',
        ]);

        $product = MarketplaceProduct::query()->create([
            'category_id' => $category->id,
            'name' => 'Software Keys',
            'slug' => 'software-keys-browse',
            'is_active' => true,
            'sort_order' => 0,
            'hero_title' => 'Software Keys',
            'seo_title' => 'Software Keys Marketplace',
        ]);

        return [$category, $product];
    }

    public function test_category_landing_resolves_before_listing_slug_collision(): void
    {
        [$category, $product] = $this->seedHierarchy();

        $seller = User::factory()->create(['email_verified_at' => now()]);
        Listing::query()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'marketplace_product_id' => $product->id,
            'title' => 'Conflicting Listing',
            'slug' => $category->slug,
            'description' => 'Should not win over category landing',
            'price' => 1000,
            'category' => $product->slug,
            'is_active' => true,
            'status' => 'published',
        ]);

        $this->get(route('marketplace.show', $category->slug))
            ->assertOk()
            ->assertSee('Digital Goods Hub')
            ->assertSee('Software Keys');
    }

    public function test_product_landing_route_renders_cms_content(): void
    {
        [$category, $product] = $this->seedHierarchy();

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $this->get(route('marketplace.product', [
            'category' => $category->slug,
            'product' => $product->slug,
        ]))
            ->assertOk()
            ->assertSee('Software Keys');
    }

    public function test_listing_show_still_works_for_non_category_slugs(): void
    {
        [$category, $product] = $this->seedHierarchy();
        $seller = User::factory()->create(['email_verified_at' => now()]);

        Listing::query()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'marketplace_product_id' => $product->id,
            'title' => 'Unique Listing',
            'slug' => 'unique-listing-slug',
            'description' => 'Published listing body',
            'price' => 2500,
            'category' => $product->slug,
            'is_active' => true,
            'status' => 'published',
        ]);

        $this->get(route('marketplace.show', 'unique-listing-slug'))
            ->assertOk()
            ->assertSee('Unique Listing');
    }

    public function test_sitemap_includes_category_and_product_landings(): void
    {
        [$category, $product] = $this->seedHierarchy();

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('marketplace.show', $category->slug), false)
            ->assertSee(route('marketplace.product', [
                'category' => $category->slug,
                'product' => $product->slug,
            ]), false);
    }
}
