<?php

namespace Tests\Feature\Marketplace;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingVersion;
use App\Models\MarketplaceProduct;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Media\MediaUsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceProductionAuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_restore_rejected_returns_to_draft_not_published(): void
    {
        $listing = Listing::factory()->rejected()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.listings.restore', $listing))
            ->assertRedirect();

        $listing->refresh();
        $this->assertSame('draft', $listing->status);
        $this->assertFalse($listing->is_active);
    }

    public function test_approve_requires_pending_version_or_pending_listing(): void
    {
        $listing = Listing::factory()->create(['status' => 'draft', 'is_active' => false]);

        $this->actingAs($this->admin())
            ->post(route('admin.listings.approve', $listing))
            ->assertRedirect()
            ->assertSessionHas('error');

        $listing->refresh();
        $this->assertSame('draft', $listing->status);
    }

    public function test_published_listing_with_pending_revision_can_be_approved(): void
    {
        $listing = Listing::factory()->published()->create([
            'title' => 'Live Title',
            'price' => 1000,
        ]);

        ListingVersion::query()->create([
            'listing_id' => $listing->id,
            'version_number' => 2,
            'title' => 'Revised Title',
            'description' => $listing->description,
            'price' => 2000,
            'status' => 'pending_review',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.listings.approve', $listing))
            ->assertRedirect()
            ->assertSessionHas('status');

        $listing->refresh();
        $this->assertSame('published', $listing->status);
        $this->assertSame('Revised Title', $listing->title);
        $this->assertEquals(2000, (float) $listing->price);
    }

    public function test_sold_listings_appear_on_sold_tab(): void
    {
        Listing::factory()->sold()->create(['title' => 'Sold Widget']);

        $this->actingAs($this->admin())
            ->get(route('admin.listings', ['status' => 'sold']))
            ->assertOk()
            ->assertSee('Sold Widget');
    }

    public function test_category_delete_blocked_when_products_exist(): void
    {
        $category = Category::factory()->create();
        MarketplaceProduct::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.marketplace-categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_product_delete_blocked_when_listings_exist(): void
    {
        $product = MarketplaceProduct::factory()->create();
        Listing::factory()->create(['marketplace_product_id' => $product->id, 'category_id' => $product->category_id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.marketplace-products.destroy', $product))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('marketplace_products', ['id' => $product->id]);
    }

    public function test_cleanup_remaps_child_category_ids_before_delete(): void
    {
        $parent = Category::factory()->create(['slug' => 'parent-net']);
        $child = Category::factory()->create([
            'slug' => 'vpn-child-cleanup',
            'parent_id' => $parent->id,
            'created_at' => now()->subDays(30),
        ]);
        $product = MarketplaceProduct::factory()->create([
            'category_id' => $parent->id,
            'slug' => 'vpn-child-cleanup',
            'name' => 'VPN',
        ]);
        $listing = Listing::factory()->published()->create([
            'marketplace_product_id' => $product->id,
            'category_id' => $child->id,
        ]);

        Artisan::call('marketplace:cleanup-child-categories', ['--force' => true, '--days' => 14]);

        $listing->refresh();
        $this->assertSame($parent->id, $listing->category_id);
        $this->assertDatabaseMissing('categories', ['id' => $child->id]);
    }

    public function test_media_replace_rewrites_marketplace_category_card(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson(route('admin.media.store'), [
                'files' => [UploadedFile::fake()->image('old.png', 400, 300)],
            ])
            ->assertCreated();
        $old = MediaAsset::query()->latest('id')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('admin.media.store'), [
                'files' => [UploadedFile::fake()->image('new.png', 420, 310)],
            ])
            ->assertCreated();
        $new = MediaAsset::query()->latest('id')->firstOrFail();

        $category = Category::factory()->create([
            'card_media_id' => $old->id,
            'banner_media_id' => $old->id,
            'card_image' => 'storage/media/old.png',
            'banner_image' => 'storage/media/old.png',
        ]);

        app(MediaUsageService::class)->syncUsages($category, [
            'card' => $old->id,
            'banner' => $old->id,
        ]);

        app(MediaUsageService::class)->replaceAsset($old->id, $new->id);

        $category->refresh();
        $this->assertSame($new->id, $category->card_media_id);
        $this->assertSame($new->id, $category->banner_media_id);
        $this->assertNotSame('storage/media/old.png', $category->card_image);
    }

    public function test_public_index_filters_by_marketplace_product(): void
    {
        $product = MarketplaceProduct::factory()->create(['name' => 'Filter Product']);
        Listing::factory()->published()->create([
            'marketplace_product_id' => $product->id,
            'category_id' => $product->category_id,
            'title' => 'Matching Listing',
        ]);
        $other = MarketplaceProduct::factory()->create();
        Listing::factory()->published()->create([
            'marketplace_product_id' => $other->id,
            'category_id' => $other->category_id,
            'title' => 'Other Listing',
        ]);

        $this->get(route('marketplace', ['product' => $product->id]))
            ->assertOk()
            ->assertSee('Matching Listing')
            ->assertDontSee('Other Listing');
    }

    public function test_destroy_audit_log_does_not_type_error(): void
    {
        $listing = Listing::factory()->rejected()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.listings.destroy', $listing))
            ->assertRedirect(route('admin.listings'));

        $this->assertSoftDeleted('listings', ['id' => $listing->id]);
    }
}
