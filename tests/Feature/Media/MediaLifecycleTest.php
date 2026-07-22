<?php

namespace Tests\Feature\Media;

use App\Models\CatalogPageContent;
use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\PlatformProduct;
use App\Models\ProductType;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\Media\MediaPathService;
use App\Services\Media\MediaUsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        return $admin;
    }

    private function makeAsset(User $admin, string $name = 'a.png', int $w = 400, int $h = 300): MediaAsset
    {
        Storage::fake('public');

        $this->actingAs($admin)
            ->postJson(route('admin.media.store'), [
                'files' => [UploadedFile::fake()->image($name, $w, $h)],
            ])
            ->assertCreated();

        return MediaAsset::query()->latest('id')->firstOrFail();
    }

    public function test_catalog_meta_writes_legacy_public_path_not_absolute_url(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $asset = $this->makeAsset($admin, 'banner.png');

        $category = ServiceCategory::query()->create([
            'name' => 'Digital',
            'slug' => 'digital-services',
            'sort_order' => 0,
            'is_active' => true,
            'mode' => 'catalog',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.catalog-pages.upsert'), [
                'scope' => 'group',
                'key' => $category->slug,
                'banner_media_id' => $asset->id,
                'card_media_id' => $asset->id,
            ])
            ->assertRedirect();

        $page = CatalogPageContent::query()->where('scope', 'group')->where('key', $category->slug)->first();
        $this->assertNotNull($page);
        $this->assertNotNull($page->banner_image);
        $this->assertStringNotContainsString('http', $page->banner_image);
        $this->assertStringStartsWith('storage/', $page->banner_image);
    }

    public function test_replace_updates_usages_and_product_hero(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $old = $this->makeAsset($admin, 'old.png', 400, 300);
        $new = $this->makeAsset($admin, 'new.png', 420, 310);

        $category = ServiceCategory::query()->create([
            'name' => 'Cat',
            'slug' => 'cat',
            'sort_order' => 0,
            'is_active' => true,
            'mode' => 'catalog',
        ]);
        $service = ProductType::query()->create([
            'name' => 'Svc',
            'slug' => 'svc',
            'service_category_id' => $category->id,
            'sort_order' => 0,
            'is_active' => true,
        ]);
        $product = PlatformProduct::query()->create([
            'title' => 'Prod',
            'slug' => 'prod',
            'product_type_id' => $service->id,
            'product_type' => 'vpn',
            'status' => 'draft',
            'base_price' => 10,
            'hero_media_id' => $old->id,
            'hero_image' => app(MediaPathService::class)->legacyPathFromMediaId($old->id),
        ]);

        app(MediaUsageService::class)->syncUsages($product, ['hero' => $old->id]);

        $this->actingAs($admin)
            ->postJson(route('admin.media.replace', $old), [
                'new_media_id' => $new->id,
            ])
            ->assertOk()
            ->assertJsonPath('updated', 1);

        $product->refresh();
        $this->assertSame($new->id, (int) $product->hero_media_id);
        $this->assertNotSame(
            app(MediaPathService::class)->legacyPathFromMediaId($old->id),
            $product->hero_image
        );
        $this->assertDatabaseHas('media_usages', [
            'media_asset_id' => $new->id,
            'usable_id' => $product->id,
            'field' => 'hero',
        ]);
    }

    public function test_destroying_category_detaches_usages(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $asset = $this->makeAsset($admin);

        $category = ServiceCategory::query()->create([
            'name' => 'To Delete',
            'slug' => 'to-delete',
            'sort_order' => 0,
            'is_active' => true,
            'mode' => 'catalog',
            'banner_media_id' => $asset->id,
        ]);
        app(MediaUsageService::class)->syncUsages($category, ['banner' => $asset->id]);

        $this->assertSame(1, app(MediaUsageService::class)->usageCount($asset->id));

        $this->actingAs($admin)
            ->delete(route('admin.service-categories.destroy', $category))
            ->assertRedirect();

        $this->assertSame(0, app(MediaUsageService::class)->usageCount($asset->id));
    }

    public function test_soft_deleted_media_cannot_be_attached(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $asset = $this->makeAsset($admin);
        $asset->delete();

        $this->actingAs($admin)
            ->post(route('admin.service-categories.store'), [
                'name' => 'Blocked',
                'mode' => 'catalog',
                'banner_media_id' => $asset->id,
            ])
            ->assertSessionHasErrors('banner_media_id');
    }

    public function test_guest_cannot_upload_media(): void
    {
        Storage::fake('public');

        $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->image('x.png', 100, 100)],
        ])->assertUnauthorized();
    }

    public function test_users_index_partial_for_ajax_tab(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->withHeaders(['X-Dashboard-Tab' => '1'])
            ->get(route('admin.users'))
            ->assertOk();
    }

    public function test_media_url_helper_prefers_asset(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $asset = $this->makeAsset($admin);

        $url = media_url($asset->load('variants'), 'images/legacy.png');
        $this->assertNotNull($url);
        $this->assertStringContainsString('storage', $url);
    }

    public function test_store_document_always_returns_array_path(): void
    {
        Storage::fake('local');
        config(['media.allowed_types' => ['image', 'document']]);

        $admin = $this->admin();
        $service = app(\App\Services\Media\MediaUploadService::class);
        $result = $service->storeDocument(
            UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf'),
            $admin
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('path', $result);
        $this->assertNotEmpty($result['path']);
        $this->assertArrayHasKey('media_asset_id', $result);
    }
}
