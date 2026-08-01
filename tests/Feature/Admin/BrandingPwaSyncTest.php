<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Branding\PwaBrandingSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
