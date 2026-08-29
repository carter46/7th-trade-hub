<?php

namespace Tests\Feature\Admin;

use App\Models\MediaAsset;
use App\Models\MediaVariant;
use App\Models\User;
use App\Services\Branding\PwaBrandingSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandingPwaSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_writes_favicon_apple_and_pwa_icons(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension required');
        }

        $ok = app(PwaBrandingSync::class)->sync([
            'site_name' => '7th Trade Hub',
            'site_short_name' => '7thHub',
            'meta_description' => 'Test',
            'favicon_media_id' => null,
            'logo_light_media_id' => null,
            'logo_dark_media_id' => null,
        ]);

        $this->assertTrue($ok);
        $this->assertFileExists(public_path('icons/icon-192x192.png'));
        $this->assertFileExists(public_path('icons/icon-512x512.png'));
        $this->assertFileExists(public_path('apple-touch-icon.png'));
        $this->assertFileExists(public_path('favicon-32x32.png'));
        $this->assertFileExists(public_path('favicon-16x16.png'));
        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertFileExists(public_path('logo.png'));
        $this->assertFileExists(public_path('manifest.json'));

        $this->get('/')->assertOk()
            ->assertSee('apple-touch-icon.png', false)
            ->assertSee('favicon-32x32.png', false)
            ->assertSee('manifest.json', false);
    }

    public function test_sync_uses_uploaded_favicon_media_not_green_fallback(): void
    {
        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagepng')) {
            $this->markTestSkipped('GD extension required');
        }

        Storage::fake('public');

        $img = imagecreatetruecolor(64, 64);
        $red = imagecolorallocate($img, 220, 20, 60);
        imagefilledrectangle($img, 0, 0, 63, 63, $red);
        ob_start();
        imagepng($img);
        $bytes = ob_get_clean();
        imagedestroy($img);

        $path = 'media/branding/favicon-source.png';
        Storage::disk('public')->put($path, $bytes);

        $asset = MediaAsset::query()->create([
            'type' => 'image',
            'disk' => 'public',
            'folder' => 'branding',
            'original_name' => 'favicon-source.png',
            'mime' => 'image/png',
            'extension' => 'png',
            'size_bytes' => strlen($bytes),
            'width' => 64,
            'height' => 64,
            'keep_original' => true,
        ]);

        MediaVariant::query()->create([
            'media_asset_id' => $asset->id,
            'key' => 'original',
            'path' => $path,
            'width' => 64,
            'height' => 64,
            'size_bytes' => strlen($bytes),
            'mime' => 'image/png',
        ]);

        $ok = app(PwaBrandingSync::class)->sync([
            'site_name' => '7th Trade Hub',
            'site_short_name' => '7thHub',
            'meta_description' => 'Test',
            'favicon_media_id' => $asset->id,
            'logo_light_media_id' => null,
            'logo_dark_media_id' => null,
        ]);

        $this->assertTrue($ok);
        $this->assertFileExists(public_path('favicon-32x32.png'));

        $info = getimagesize(public_path('favicon-32x32.png'));
        $this->assertNotFalse($info);
        $sampled = imagecreatefrompng(public_path('favicon-32x32.png'));
        $this->assertNotFalse($sampled);
        $rgb = imagecolorat($sampled, 16, 16);
        imagedestroy($sampled);
        $r = ($rgb >> 16) & 0xFF;
        // Source is red — must not be the green theme fallback (~11,106,57).
        $this->assertGreaterThan(150, $r);
    }

    public function test_should_regenerate_icons_when_favicon_media_is_newer(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension required');
        }

        Storage::fake('public');

        $img = imagecreatetruecolor(32, 32);
        $blue = imagecolorallocate($img, 30, 90, 200);
        imagefilledrectangle($img, 0, 0, 31, 31, $blue);
        ob_start();
        imagepng($img);
        $bytes = ob_get_clean();
        imagedestroy($img);

        $path = 'media/branding/favicon-new.png';
        Storage::disk('public')->put($path, $bytes);

        $asset = MediaAsset::query()->create([
            'type' => 'image',
            'disk' => 'public',
            'folder' => 'branding',
            'original_name' => 'favicon-new.png',
            'mime' => 'image/png',
            'extension' => 'png',
            'size_bytes' => strlen($bytes),
            'width' => 32,
            'height' => 32,
            'keep_original' => true,
        ]);

        MediaVariant::query()->create([
            'media_asset_id' => $asset->id,
            'key' => 'original',
            'path' => $path,
            'width' => 32,
            'height' => 32,
            'size_bytes' => strlen($bytes),
            'mime' => 'image/png',
        ]);

        $sync = app(PwaBrandingSync::class);
        $branding = [
            'site_name' => '7th Trade Hub',
            'site_short_name' => '7thHub',
            'meta_description' => 'Test',
            'favicon_media_id' => $asset->id,
            'logo_light_media_id' => null,
            'logo_dark_media_id' => null,
        ];

        $this->assertTrue($sync->sync($branding));
        touch(public_path('icons/icon-512x512.png'), time() - 3600);

        $this->assertTrue($sync->shouldRegenerateIcons($branding));
    }

    public function test_admin_branding_save_reports_sync_status(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.settings.branding'), [
                'site_name' => '7th Trade Hub',
                'site_short_name' => '7thHub',
                'heading' => '',
                'tagline' => '',
                'meta_description' => '',
            ])
            ->assertRedirect();
    }
}
