<?php

namespace Tests\Unit\Branding;

use App\Models\MediaAsset;
use App\Models\MediaVariant;
use App\Services\Branding\EmailLogoResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailLogoResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_prefers_png_original_over_webp_medium(): void
    {
        Storage::fake('public');
        config(['app.url' => 'https://7th-tradehub.online']);

        $asset = MediaAsset::query()->create([
            'uuid' => (string) Str::uuid(),
            'type' => 'image',
            'disk' => 'public',
            'folder' => 'branding',
            'original_name' => 'logo.png',
            'mime' => 'image/png',
            'extension' => 'png',
            'size_bytes' => 100,
            'keep_original' => true,
        ]);

        Storage::disk('public')->put('media/branding/original.png', 'png-bytes');
        Storage::disk('public')->put('media/branding/medium.webp', 'webp-bytes');

        MediaVariant::query()->create([
            'media_asset_id' => $asset->id,
            'key' => 'original',
            'path' => 'media/branding/original.png',
            'mime' => 'image/png',
            'width' => 200,
            'height' => 80,
            'size_bytes' => 100,
        ]);
        MediaVariant::query()->create([
            'media_asset_id' => $asset->id,
            'key' => 'medium',
            'path' => 'media/branding/medium.webp',
            'mime' => 'image/webp',
            'width' => 200,
            'height' => 80,
            'size_bytes' => 80,
        ]);

        $url = app(EmailLogoResolver::class)->absoluteUrl($asset->id);

        $this->assertNotNull($url);
        $this->assertStringContainsString('original.png', $url);
        $this->assertStringNotContainsString('.webp', $url);
        $this->assertStringStartsWith('https://7th-tradehub.online', $url);
    }

    public function test_materializes_png_when_only_webp_exists(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD required');
        }

        Storage::fake('public');
        config(['app.url' => 'https://7th-tradehub.online']);

        $img = imagecreatetruecolor(8, 8);
        imagesavealpha($img, true);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);
        $tmp = tempnam(sys_get_temp_dir(), 'webpsrc');
        imagepng($img, $tmp);
        imagedestroy($img);
        $sourceBytes = (string) file_get_contents($tmp);
        @unlink($tmp);

        $asset = MediaAsset::query()->create([
            'uuid' => (string) Str::uuid(),
            'type' => 'image',
            'disk' => 'public',
            'folder' => 'branding',
            'original_name' => 'logo.webp',
            'mime' => 'image/webp',
            'extension' => 'webp',
            'size_bytes' => strlen($sourceBytes),
            'keep_original' => false,
        ]);

        // Stored as .webp path/mime even if bytes are PNG — mirrors “derivative labeled webp”.
        Storage::disk('public')->put('media/branding/medium.webp', $sourceBytes);

        MediaVariant::query()->create([
            'media_asset_id' => $asset->id,
            'key' => 'medium',
            'path' => 'media/branding/medium.webp',
            'mime' => 'image/webp',
            'width' => 8,
            'height' => 8,
            'size_bytes' => strlen($sourceBytes),
        ]);

        $url = app(EmailLogoResolver::class)->absoluteUrl($asset->id);

        $this->assertNotNull($url);
        $this->assertStringContainsString('email-safe/'.$asset->uuid.'.png', $url);
        $this->assertStringStartsWith('https://7th-tradehub.online/storage/media/email-safe/', $url);
        $this->assertTrue(Storage::disk('public')->exists('media/email-safe/'.$asset->uuid.'.png'));
    }
}
