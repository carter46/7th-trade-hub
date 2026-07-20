<?php

namespace Tests\Feature\Catalog;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Models\PlatformCategory;
use App\Models\PlatformProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicesHubTest extends TestCase
{
    use RefreshDatabase;

    private function seedVpnProduct(string $slug = 'residential-vpn-demo', bool $featured = false): PlatformProduct
    {
        $category = PlatformCategory::create([
            'name' => 'Residential',
            'slug' => 'vpn-residential-test-'.uniqid(),
            'product_type' => PlatformProductType::Vpn,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return PlatformProduct::create([
            'platform_category_id' => $category->id,
            'product_type' => PlatformProductType::Vpn,
            'title' => 'Residential VPN Demo',
            'slug' => $slug,
            'short_description' => 'Test VPN product',
            'description' => 'A test VPN',
            'status' => PlatformProductStatus::Published,
            'is_featured' => $featured,
            'base_price' => 3500,
            'sort_order' => 1,
        ]);
    }

    public function test_services_landing_shows_groups_not_product_grid(): void
    {
        $this->seedVpnProduct();

        $this->get(route('services'))
            ->assertOk()
            ->assertSee('Network Services')
            ->assertSee('Social Media')
            ->assertSee('Browse Categories')
            ->assertDontSee('Residential VPN Demo');
    }

    public function test_group_page_shows_type_cards(): void
    {
        $this->seedVpnProduct();

        $this->get(route('services.segment', 'network-services'))
            ->assertOk()
            ->assertSee('Network Services')
            ->assertSee('VPN');
    }

    public function test_type_page_shows_featured_and_products(): void
    {
        $this->seedVpnProduct(featured: true);

        $this->get(route('services.segment', 'vpn'))
            ->assertOk()
            ->assertSee('Featured')
            ->assertSee('Residential VPN Demo');
    }

    public function test_type_page_filters_by_category(): void
    {
        $product = $this->seedVpnProduct();
        $other = PlatformCategory::create([
            'name' => 'Gaming',
            'slug' => 'vpn-gaming-test-'.uniqid(),
            'product_type' => PlatformProductType::Vpn,
            'is_active' => true,
        ]);

        PlatformProduct::create([
            'platform_category_id' => $other->id,
            'product_type' => PlatformProductType::Vpn,
            'title' => 'Gaming VPN Only',
            'slug' => 'gaming-vpn-only-'.uniqid(),
            'status' => PlatformProductStatus::Published,
            'base_price' => 4000,
        ]);

        $this->get(route('services.segment', ['segment' => 'vpn', 'category' => $product->platform_category_id]))
            ->assertOk()
            ->assertSee('Residential VPN Demo')
            ->assertDontSee('Gaming VPN Only');
    }

    public function test_product_show_url_and_legacy_redirect(): void
    {
        $this->seedVpnProduct('legacy-vpn-slug');

        $this->get(route('services.show', ['type' => 'vpn', 'productSlug' => 'legacy-vpn-slug']))
            ->assertOk()
            ->assertSee('Residential VPN Demo');

        $this->get('/services/legacy-vpn-slug')
            ->assertRedirect('/services/vpn/legacy-vpn-slug');
    }

    public function test_services_search_finds_products(): void
    {
        $this->seedVpnProduct();

        $this->get(route('services', ['q' => 'Residential VPN']))
            ->assertOk()
            ->assertSee('Search results')
            ->assertSee('Residential VPN Demo');
    }

    public function test_unknown_segment_returns_404(): void
    {
        $this->get('/services/not-a-real-group-or-type')
            ->assertNotFound();
    }

    public function test_legacy_division_redirects_to_group(): void
    {
        $this->get('/services/digital-services')
            ->assertRedirect('/services/network-services');
    }

    public function test_wrong_type_in_product_url_redirects_to_canonical(): void
    {
        $this->seedVpnProduct('canonical-vpn-slug');

        $this->get('/services/email/canonical-vpn-slug')
            ->assertRedirect('/services/vpn/canonical-vpn-slug');
    }

    public function test_category_filter_uses_category_hero_override(): void
    {
        $product = $this->seedVpnProduct();
        $product->category->update([
            'hero_title' => 'Residential Only Hero',
            'short_description' => 'Category short copy',
        ]);

        $this->get(route('services.segment', ['segment' => 'vpn', 'category' => $product->platform_category_id]))
            ->assertOk()
            ->assertSee('Residential Only Hero');
    }
}
