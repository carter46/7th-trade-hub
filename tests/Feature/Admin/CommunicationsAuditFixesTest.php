<?php

namespace Tests\Feature\Admin;

use App\Models\IntegrationProvider;
use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Communications\Email\EmailProfile;
use App\Services\Communications\Email\Providers\LaravelMailProvider;
use App\Services\Communications\LiveChat\LiveChatManager;
use App\Services\Media\MediaUsageService;
use App\Services\Notifications\Channels\MailChannel;
use App\Services\Notifications\NotificationMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use ReflectionMethod;
use Tests\TestCase;

class CommunicationsAuditFixesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_live_chat_secrets_are_not_stored_in_system_settings(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.settings.contact'), [
                'live_chat_provider' => 'smartsupp',
                'smartsupp_key' => 'secret-smartsupp-key',
                'jivo_widget_id' => '',
                'chatway_widget_id' => '',
                'timezone' => 'Africa/Lagos',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('smartsupp', SystemSetting::get('live_chat_provider'));
        $this->assertSame('', (string) SystemSetting::get('smartsupp_key', ''));
        $this->assertSame('', (string) SystemSetting::get('jivo_widget_id', ''));
        $this->assertSame(
            'secret-smartsupp-key',
            IntegrationProvider::forProvider(IntegrationProvider::SMARTSUPP)->credential('key')
        );
    }

    public function test_chatway_widget_id_does_not_pollute_jivo_credentials(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.settings.contact'), [
                'live_chat_provider' => 'chatway',
                'smartsupp_key' => '',
                'jivo_widget_id' => '',
                'chatway_widget_id' => 'chatway-widget-abc',
                'timezone' => 'Africa/Lagos',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $resolved = app(LiveChatManager::class)->resolved();
        $this->assertSame('chatway', $resolved['provider']);
        $this->assertSame('chatway-widget-abc', $resolved['credentials']['widget_id'] ?? null);
        $this->assertNull(IntegrationProvider::forProvider(IntegrationProvider::JIVO)->credential('widget_id'));
        $this->assertFalse(IntegrationProvider::forProvider(IntegrationProvider::JIVO)->enabled);
    }

    public function test_jivo_widget_uses_official_script_path(): void
    {
        $row = IntegrationProvider::forProvider(IntegrationProvider::JIVO);
        $row->enabled = true;
        $row->mergeCredentials(['widget_id' => 'JvWidgetId99']);
        $row->save();

        IntegrationProvider::forProvider(IntegrationProvider::SMARTSUPP)->forceFill(['enabled' => false])->save();
        IntegrationProvider::forProvider(IntegrationProvider::CHATWAY)->forceFill(['enabled' => false])->save();

        $html = view('partials.marketing.live-chat-widget')->render();

        $this->assertStringContainsString('https://code.jivosite.com/script/widget/JvWidgetId99', $html);
        $this->assertStringNotContainsString('https://code.jivosite.com/widget/JvWidgetId99', $html);
    }

    public function test_laravel_mail_disabled_is_not_available(): void
    {
        config(['mail.default' => 'smtp']);

        $row = IntegrationProvider::forProvider(IntegrationProvider::LARAVEL_MAIL);
        $row->enabled = false;
        $row->save();

        $this->assertFalse(app(LaravelMailProvider::class)->isAvailable());
    }

    public function test_mail_channel_maps_ticket_notifications_to_support_profile(): void
    {
        $channel = app(MailChannel::class);
        $method = new ReflectionMethod(MailChannel::class, 'profileFor');
        $method->setAccessible(true);

        $profile = $method->invoke($channel, new NotificationMessage(
            type: 'ticket.replied',
            title: 'Support replied',
            body: 'Body',
        ));

        $this->assertSame(EmailProfile::Support, $profile);
    }

    public function test_branding_media_replace_rewrites_system_settings(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson(route('admin.media.store'), [
                'files' => [UploadedFile::fake()->image('old-logo.png', 200, 80)],
            ])
            ->assertCreated();
        $old = MediaAsset::query()->latest('id')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('admin.media.store'), [
                'files' => [UploadedFile::fake()->image('new-logo.png', 220, 90)],
            ])
            ->assertCreated();
        $new = MediaAsset::query()->latest('id')->firstOrFail();

        SystemSetting::set('logo_light_media_id', (string) $old->id);
        MediaUsage::query()->create([
            'media_asset_id' => $old->id,
            'usable_type' => 'site_branding',
            'usable_id' => 1,
            'field' => 'logo_light',
        ]);

        app(MediaUsageService::class)->replaceAsset($old->id, $new->id);

        $this->assertSame((string) $new->id, (string) SystemSetting::get('logo_light_media_id'));
    }

    public function test_settings_page_masks_smartsupp_key(): void
    {
        $admin = $this->admin();
        $row = IntegrationProvider::forProvider(IntegrationProvider::SMARTSUPP);
        $row->enabled = true;
        $row->mergeCredentials(['key' => 'should-not-echo']);
        $row->save();

        $this->actingAs($admin)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertDontSee('should-not-echo')
            ->assertSee('Key is set.');
    }
}
