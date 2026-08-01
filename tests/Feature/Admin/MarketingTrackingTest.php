<?php

namespace Tests\Feature\Admin;

use App\Models\IntegrationProvider;
use App\Models\SystemSetting;
use App\Models\TrackingScript;
use App\Models\User;
use App\Services\Tracking\TrackingDuplicateDetector;
use App\Services\Tracking\TrackingScriptRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MarketingTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_guest_cannot_access_tracking_settings(): void
    {
        $this->get(route('admin.tracking'))->assertRedirect();
    }

    public function test_non_admin_cannot_access_tracking_settings(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('admin.tracking'))
            ->assertForbidden();
    }

    public function test_admin_can_save_official_providers_and_verification(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.tracking.providers'), [
                'gtm_enabled' => '1',
                'gtm_container_id' => 'GTM-ABC1234',
                'google_enabled' => '1',
                'google_measurement_id' => 'G-LDQPBEL1M9',
                'google_property_id' => '',
                'clarity_enabled' => '1',
                'clarity_project_id' => 'abcdef1234',
                'meta_enabled' => '1',
                'meta_pixel_id' => '123456789012345',
                'verification_google' => 'google-token',
                'verification_bing' => 'bing-token',
                'verification_facebook' => 'fb-token',
            ])
            ->assertRedirect(route('admin.tracking'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_TAG_MANAGER)->enabled);
        $this->assertSame('GTM-ABC1234', IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_TAG_MANAGER)->credential('container_id'));
        $this->assertSame('G-LDQPBEL1M9', IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_ANALYTICS)->credential('measurement_id'));
        $this->assertSame('123456789012345', IntegrationProvider::forProvider(IntegrationProvider::META_PIXEL)->credential('pixel_id'));
        $this->assertSame('google-token', SystemSetting::get('verification_google'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'settings.tracking.updated']);
    }

    public function test_rejects_pasted_full_ga_script_as_measurement_id(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('admin.tracking'))
            ->post(route('admin.tracking.providers'), [
                'google_enabled' => '1',
                'google_measurement_id' => "<!-- Google tag -->\n<script>gtag('config','G-XXX');</script>",
                'gtm_enabled' => '0',
                'clarity_enabled' => '0',
                'meta_enabled' => '0',
            ])
            ->assertRedirect(route('admin.tracking'))
            ->assertSessionHasErrors('google_measurement_id');
    }

    public function test_gtm_renders_head_and_body_noscript_on_home(): void
    {
        $gtm = IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_TAG_MANAGER);
        $gtm->fill(['enabled' => true, 'status' => 'configured']);
        $gtm->mergeCredentials(['container_id' => 'GTM-TEST99']);
        $gtm->save();

        app(TrackingScriptRenderer::class)->flushCache();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('googletagmanager.com/gtm.js?id=', false)
            ->assertSee('GTM-TEST99', false)
            ->assertSee('googletagmanager.com/ns.html?id=GTM-TEST99', false);
    }

    public function test_disabled_provider_emits_nothing(): void
    {
        $ga = IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_ANALYTICS);
        $ga->fill(['enabled' => false, 'status' => 'idle']);
        $ga->mergeCredentials(['measurement_id' => 'G-DISABLED1']);
        $ga->save();

        app(TrackingScriptRenderer::class)->flushCache();

        $html = app(TrackingScriptRenderer::class)->headHtml();
        $this->assertStringNotContainsString('G-DISABLED1', $html);
    }

    public function test_custom_script_appears_only_in_chosen_location(): void
    {
        TrackingScript::query()->create([
            'name' => 'Affiliate',
            'location' => 'body_end',
            'enabled' => true,
            'code' => '<script>window.__affiliate=1</script>',
            'sort_order' => 1,
        ]);

        app(TrackingScriptRenderer::class)->flushCache();
        $renderer = app(TrackingScriptRenderer::class);

        $this->assertStringNotContainsString('__affiliate', $renderer->headHtml());
        $this->assertStringNotContainsString('__affiliate', $renderer->bodyStartHtml());
        $this->assertStringContainsString('__affiliate', $renderer->bodyEndHtml());
    }

    public function test_duplicate_detector_warns_when_gtm_pasted_and_official_enabled(): void
    {
        $gtm = IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_TAG_MANAGER);
        $gtm->fill(['enabled' => true, 'status' => 'configured']);
        $gtm->mergeCredentials(['container_id' => 'GTM-ABC1234']);
        $gtm->save();

        $script = TrackingScript::query()->create([
            'name' => 'GTM Paste',
            'location' => 'head',
            'enabled' => true,
            'code' => '<script src="https://www.googletagmanager.com/gtm.js?id=GTM-ABC1234"></script>',
            'sort_order' => 1,
        ]);

        $conflicts = app(TrackingDuplicateDetector::class)->conflicts($script);
        $this->assertNotEmpty($conflicts);
        $this->assertSame(IntegrationProvider::GOOGLE_TAG_MANAGER, $conflicts[0]['provider']);
        $this->assertTrue($conflicts[0]['official_enabled']);
    }

    public function test_saving_duplicate_custom_script_still_succeeds_with_warning(): void
    {
        $admin = $this->admin();

        $meta = IntegrationProvider::forProvider(IntegrationProvider::META_PIXEL);
        $meta->fill(['enabled' => true, 'status' => 'configured']);
        $meta->mergeCredentials(['pixel_id' => '1234567890']);
        $meta->save();

        $this->actingAs($admin)
            ->post(route('admin.tracking.scripts.store'), [
                'name' => 'FB Paste',
                'location' => 'head',
                'enabled' => '1',
                'code' => '<script>fbq("init","1234567890");</script>',
            ])
            ->assertRedirect(route('admin.tracking'))
            ->assertSessionHas('warning')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tracking_scripts', ['name' => 'FB Paste']);
    }

    public function test_preview_inventory_order_matches_injection_order(): void
    {
        SystemSetting::set('verification_google', 'tok');
        $gtm = IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_TAG_MANAGER);
        $gtm->fill(['enabled' => true, 'status' => 'configured']);
        $gtm->mergeCredentials(['container_id' => 'GTM-ORDER1']);
        $gtm->save();
        $ga = IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_ANALYTICS);
        $ga->fill(['enabled' => true, 'status' => 'configured']);
        $ga->mergeCredentials(['measurement_id' => 'G-ORDER1']);
        $ga->save();

        TrackingScript::query()->create([
            'name' => 'Hotjar',
            'location' => 'head',
            'enabled' => true,
            'code' => '<script>window.hj=1</script>',
            'sort_order' => 1,
        ]);

        app(TrackingScriptRenderer::class)->flushCache();
        $preview = app(TrackingScriptRenderer::class)->itemsForPreview();
        $labels = array_column($preview['head'], 'label');

        $this->assertSame('Google site verification', $labels[0]);
        $this->assertSame('Google Tag Manager', $labels[1]);
        $this->assertSame('Google Analytics', $labels[2]);
        $this->assertContains('Custom: Hotjar', $labels);
    }

    public function test_cache_busts_after_admin_save(): void
    {
        $admin = $this->admin();
        $renderer = app(TrackingScriptRenderer::class);

        Cache::put(TrackingScriptRenderer::CACHE_KEY.':stale', [
            'head' => '<!-- stale -->',
            'body_start' => '',
            'body_end' => '',
        ], 3600);
        Cache::put(TrackingScriptRenderer::CACHE_KEY.'.stamp', 'stale', 3600);

        $this->actingAs($admin)
            ->post(route('admin.tracking.providers'), [
                'google_enabled' => '1',
                'google_measurement_id' => 'G-CACHEBUST1',
                'gtm_enabled' => '0',
                'clarity_enabled' => '0',
                'meta_enabled' => '0',
            ])
            ->assertRedirect(route('admin.tracking'));

        $this->assertFalse(Cache::has(TrackingScriptRenderer::CACHE_KEY.':stale'));
        $this->assertStringContainsString('G-CACHEBUST1', $renderer->headHtml());
    }

    public function test_tracking_not_injected_on_admin_dashboard(): void
    {
        $admin = $this->admin();

        $ga = IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_ANALYTICS);
        $ga->fill(['enabled' => true, 'status' => 'configured']);
        $ga->mergeCredentials(['measurement_id' => 'G-ADMINNO1']);
        $ga->save();
        app(TrackingScriptRenderer::class)->flushCache();

        $this->actingAs($admin)
            ->get(route('admin'))
            ->assertOk()
            ->assertDontSee('G-ADMINNO1', false);
    }
}
