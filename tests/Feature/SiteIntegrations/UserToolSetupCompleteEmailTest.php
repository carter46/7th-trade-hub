<?php

namespace Tests\Feature\SiteIntegrations;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Enums\UserToolStatus;
use App\Models\User;
use App\Models\UserTool;
use App\Services\Communications\Email\EmailService;
use App\Services\Communications\Email\SendResult;
use App\Services\SiteIntegrations\UserToolProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class UserToolSetupCompleteEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_setup_emails_member_with_my_tools_link(): void
    {
        $member = User::factory()->create([
            'email_verified_at' => now(),
            'email' => 'member-setup@example.com',
        ]);
        $member->assignRole('user');

        $product = $this->forceCreatePlatformProduct([
            'title' => 'Online banking setup mail',
            'slug' => 'ob-setup-mail-'.Str::lower(Str::random(4)),
            'product_type' => PlatformProductType::WebsitePackage,
            'status' => PlatformProductStatus::Published,
            'base_price' => 10000,
            'sort_order' => 1,
            'provider' => 'manual',
            'fulfillment_mode' => 'manual',
        ]);

        $tool = UserTool::query()->create([
            'user_id' => $member->id,
            'platform_product_id' => $product->id,
            'status' => UserToolStatus::PendingSetup,
            'display_name' => $product->title,
            'purchased_at' => now(),
            'duration_months' => 3,
            'instance_sequence' => 1,
        ]);

        $captured = null;
        $emails = Mockery::mock(EmailService::class);
        $emails->shouldReceive('sendMailableHtml')
            ->once()
            ->withArgs(function ($to, $subject, $html) use ($member, $tool, &$captured) {
                $captured = compact('to', 'subject', 'html');

                return $to === $member->email
                    && str_contains((string) $subject, 'setup complete')
                    && str_contains((string) $html, route('dashboard.my-tools.show', $tool))
                    && str_contains((string) $html, 'View tool');
            })
            ->andReturn(SendResult::ok('brevo', 'setup-1'));
        $this->app->instance(EmailService::class, $emails);
        $this->app->forgetInstance(\App\Services\Communications\Email\OutboundMail::class);
        $this->app->forgetInstance(\App\Services\Notifications\Channels\MailChannel::class);
        $this->app->forgetInstance(\App\Services\Notifications\NotificationDispatcher::class);
        $this->app->forgetInstance(\App\Services\SiteIntegrations\UserToolLifecycleNotifier::class);
        $this->app->forgetInstance(UserToolProvisioningService::class);

        app(UserToolProvisioningService::class)->setup($tool, [
            'site_url' => 'https://customer-setup.example.com',
            'admin_login_url' => 'https://customer-setup.example.com/admin',
            'admin_email' => 'admin@example.com',
            'admin_password' => 'SecretPass123!',
        ]);

        $this->assertNotNull($captured);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $member->id,
            'type' => 'tool.setup_complete',
        ]);
    }
}
