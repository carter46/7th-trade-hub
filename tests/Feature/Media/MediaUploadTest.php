<?php

namespace Tests\Feature\Media;

use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_image_to_media_library(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $file = UploadedFile::fake()->image('hero.png', 800, 600);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.media.store'), [
                'files' => [$file],
            ]);

        $response->assertCreated()
            ->assertJsonPath('assets.0.original_name', 'hero.png')
            ->assertJsonPath('assets.0.type', 'image')
            ->assertJsonPath('data.0.original_name', 'hero.png');

        $this->assertDatabaseHas('media_assets', [
            'original_name' => 'hero.png',
            'type' => 'image',
        ]);

        $asset = MediaAsset::query()->first();
        $this->assertNotNull($asset);
        $this->assertGreaterThanOrEqual(4, $asset->variants()->count());
        $this->assertNotNull($asset->url('medium'));
        $this->assertNotNull($asset->thumbnailUrl());
    }

    public function test_upload_rejects_oversized_image(): void
    {
        Storage::fake('public');

        config(['media.max_upload_size_kb' => 100]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $file = UploadedFile::fake()->image('big.jpg', 1200, 1200)->size(512);

        $this->actingAs($admin)
            ->postJson(route('admin.media.store'), [
                'files' => [$file],
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('media_assets', 0);
    }

    public function test_cannot_delete_media_that_is_in_use(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $asset = MediaAsset::query()->create([
            'type' => 'image',
            'disk' => 'public',
            'folder' => '2026/07',
            'original_name' => 'used.png',
            'mime' => 'image/webp',
            'extension' => 'webp',
            'size_bytes' => 1024,
            'width' => 100,
            'height' => 100,
            'checksum' => hash('sha256', 'used-media'),
            'uploaded_by' => $admin->id,
        ]);

        MediaUsage::query()->create([
            'media_asset_id' => $asset->id,
            'usable_type' => User::class,
            'usable_id' => $admin->id,
            'field' => 'avatar',
        ]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.media.destroy', $asset))
            ->assertStatus(422)
            ->assertJsonPath('usage_count', 1);

        $this->assertDatabaseHas('media_assets', ['id' => $asset->id]);
    }
}
