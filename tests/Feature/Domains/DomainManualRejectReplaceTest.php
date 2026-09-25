<?php

namespace Tests\Feature\Domains;

use App\Models\DomainQuote;
use App\Models\DomainRegistration;
use App\Models\EmailIdentity;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\Communications\Email\EmailProfile;
use App\Services\Communications\Email\EmailService;
use App\Services\Communications\Email\OutgoingEmail;
use App\Services\Domains\DomainRegistrationFulfillmentService;
use App\Services\Notifications\NotificationEmailRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainManualRejectReplaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Canonical mail path: Dispatcher → MailChannel → OutboundMail → EmailService.
        $emails = \Mockery::mock(EmailService::class)->makePartial();
        $emails->shouldReceive('sendMailableHtml')
            ->byDefault()
            ->andReturn(\App\Services\Communications\Email\SendResult::ok('test'));
        $this->app->instance(EmailService::class, $emails);
        $this->app->forgetInstance(\App\Services\Communications\Email\OutboundMail::class);
        $this->app->forgetInstance(\App\Services\Notifications\Channels\MailChannel::class);
        $this->app->forgetInstance(\App\Services\Notifications\NotificationDispatcher::class);
    }

    /**
     * @return array{0: User, 1: DomainRegistration}
     */
    private function seedManualPendingRegistration(): array
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        $order = Order::query()->create([
            'source' => 'platform',
            'user_id' => $user->id,
            'reference' => 'PLT-MAN01',
            'amount' => 25000,
            'total_amount' => 25000,
            'status' => 'paid',
            'payment_method' => 'wallet',
        ]);

        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'item_type' => 'platform_product',
            'item_id' => 1,
            'quantity' => 1,
            'unit_price' => 15000,
            'line_total' => 15000,
            'options' => [
                'domain_fqdn' => 'reject-me.com',
                'domain_mode' => 'buy',
                'tld' => 'com',
                'product_title' => 'Domain Registration',
                'domain_fulfillment' => 'manual',
            ],
        ]);

        $registration = DomainRegistration::query()->create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'fqdn' => 'reject-me.com',
            'provider_key' => DomainQuote::PROVIDER_KEY_MANUAL,
            'provider_cost_at_checkout' => 0,
            'provider_currency_at_checkout' => 'NGN',
            'status' => DomainRegistration::STATUS_PENDING_MANUAL,
            'provider_meta' => [
                'fulfillment' => 'manual',
                'domain_fulfillment' => 'manual',
            ],
            'error_message' => 'Awaiting manual domain registration by admin.',
        ]);

        return [$user, $registration];
    }

    public function test_manual_reject_replace_and_approve_flow(): void
    {
        [$user, $registration] = $this->seedManualPendingRegistration();
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('users.manage');

        $service = app(DomainRegistrationFulfillmentService::class);

        $rejected = $service->rejectManualRegistration($registration, 'Trademark conflict on this name.', $admin->id);
        $this->assertSame(DomainRegistration::STATUS_REJECTED, $rejected->status);
        $this->assertSame('reject-me.com', $rejected->rejectedFqdn());
        $this->assertStringContainsString('Trademark', (string) $rejected->error_message);

        $this->actingAs($user)
            ->post(route('dashboard.my-domains.replace', $rejected), [
                'fqdn' => 'replacement-ok.com',
            ])
            ->assertRedirect(route('dashboard.my-domains.show', $rejected));

        $rejected->refresh();
        $this->assertSame(DomainRegistration::STATUS_PENDING_REPLACEMENT, $rejected->status);
        $this->assertSame('replacement-ok.com', $rejected->fqdn);
        $this->assertSame('reject-me.com', $rejected->rejectedFqdn());

        $this->actingAs($admin)
            ->get(route('admin.users.domains.registrations.show', [$user, $rejected]))
            ->assertOk()
            ->assertSee('replacement-ok.com')
            ->assertSee('Reject domain');

        $approvedTuple = $service->markManualRegistered($rejected, 'OFFLINE-1', null, $admin->id);
        $this->assertFalse($approvedTuple[1]);
        $this->assertSame(DomainRegistration::STATUS_REGISTERED, $approvedTuple[0]->status);
        $this->assertSame('replacement-ok.com', $approvedTuple[0]->fqdn);
    }

    public function test_reject_and_approve_dispatch_user_mail_notifications(): void
    {
        [$user, $registration] = $this->seedManualPendingRegistration();
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $dispatcher = \Mockery::mock(\App\Services\Notifications\NotificationDispatcher::class);
        $dispatcher->shouldReceive('notifyUser')
            ->once()
            ->withArgs(function ($notifiable, $message, $channels) use ($user) {
                return (int) $notifiable->id === (int) $user->id
                    && $message->type === 'order.domain_rejected'
                    && $channels === ['database', 'mail']
                    && filled($message->dedupeKey);
            })
            ->andReturn(\App\Services\Communications\Email\SendResult::ok('test'));
        $dispatcher->shouldReceive('notifyAdmins')->once();
        $dispatcher->shouldReceive('notifyUser')
            ->once()
            ->withArgs(function ($notifiable, $message, $channels) use ($user) {
                return (int) $notifiable->id === (int) $user->id
                    && $message->type === 'order.domain_approved'
                    && $channels === ['database', 'mail'];
            })
            ->andReturn(\App\Services\Communications\Email\SendResult::ok('test'));

        $this->app->instance(\App\Services\Notifications\NotificationDispatcher::class, $dispatcher);

        $service = $this->app->make(DomainRegistrationFulfillmentService::class);
        $rejected = $service->rejectManualRegistration($registration, 'Not available at registrar.', $admin->id);
        $this->assertSame(DomainRegistration::STATUS_REJECTED, $rejected->status);

        $pending = $service->requestManualReplacement($rejected, 'approved-now.com', $user);
        $this->assertSame(DomainRegistration::STATUS_PENDING_REPLACEMENT, $pending->status);

        [$approved] = $service->markManualRegistered($pending, null, null, $admin->id);
        $this->assertSame(DomainRegistration::STATUS_REGISTERED, $approved->status);
    }

    public function test_provider_domain_cannot_use_manual_reject(): void
    {
        [$user, $registration] = $this->seedManualPendingRegistration();
        $registration->update([
            'provider_key' => 'namecom',
            'provider_meta' => ['fulfillment' => 'provider'],
        ]);

        $this->expectException(\InvalidArgumentException::class);
        app(DomainRegistrationFulfillmentService::class)
            ->rejectManualRegistration($registration->fresh(), 'Nope', null);
    }

    public function test_admin_tools_lists_domain_registration(): void
    {
        [$user, $registration] = $this->seedManualPendingRegistration();
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('users.manage');

        $this->actingAs($admin)
            ->get(route('admin.users.tools', $user))
            ->assertOk()
            ->assertSee('reject-me.com')
            ->assertSee('Manage domain');
    }

    public function test_order_email_context_includes_domain_line_details(): void
    {
        $user = User::factory()->create();
        $order = Order::query()->create([
            'source' => 'platform',
            'user_id' => $user->id,
            'reference' => 'PLT-MAIL1',
            'amount' => 40000,
            'total_amount' => 40000,
            'status' => 'paid',
            'payment_method' => 'wallet',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'item_type' => 'platform_product',
            'item_id' => 1,
            'quantity' => 1,
            'unit_price' => 25000,
            'line_total' => 25000,
            'options' => [
                'product_title' => 'Starter Website',
                'domain_mode' => 'buy',
                'domain_fqdn' => 'shop.example',
            ],
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'item_type' => 'platform_product',
            'item_id' => 2,
            'quantity' => 1,
            'unit_price' => 15000,
            'line_total' => 15000,
            'options' => [
                'product_title' => 'Domain Registration',
                'domain_fqdn' => 'shop.example',
                'domain_mode' => 'buy',
                'tld' => 'example',
                'domain_quote_id' => 99,
                'domain_fulfillment' => 'manual',
            ],
        ]);

        $context = app(NotificationEmailRenderer::class)->orderContext($order->fresh());

        $this->assertSame('PLT-MAIL1', $context['order_reference']);
        $this->assertSame('Wallet', $context['payment_method_label']);
        $this->assertNotEmpty($context['lines']);
        $titles = collect($context['lines'])->pluck('title')->implode(' ');
        $this->assertStringContainsString('shop.example', $titles);
        $metas = collect($context['lines'])->pluck('meta')->filter()->implode(' ');
        $this->assertStringContainsString('Manual', $metas);
    }

    public function test_reject_route_sends_mail_via_sales_profile_through_outbound_mail(): void
    {
        [$user, $registration] = $this->seedManualPendingRegistration();
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('users.manage');

        $emails = \Mockery::mock(EmailService::class);
        $emails->shouldReceive('sendMailableHtml')
            ->once()
            ->withArgs(function ($to, $subject, $html, $text, $profile) use ($user) {
                return $to === $user->email
                    && is_string($subject)
                    && str_contains($subject, 'reject-me.com')
                    && is_string($html)
                    && $html !== ''
                    && $profile === EmailProfile::Sales;
            })
            ->andReturn(\App\Services\Communications\Email\SendResult::ok('brevo', 'msg-1'));
        $this->app->instance(EmailService::class, $emails);
        $this->app->forgetInstance(\App\Services\Communications\Email\OutboundMail::class);
        $this->app->forgetInstance(\App\Services\Notifications\Channels\MailChannel::class);
        $this->app->forgetInstance(\App\Services\Notifications\NotificationDispatcher::class);
        $this->app->forgetInstance(DomainRegistrationFulfillmentService::class);

        $this->actingAs($admin)
            ->post(route('admin.users.domains.registrations.reject', [$user, $registration]), [
                'reason' => 'Name not available at the registrar right now.',
            ])
            ->assertRedirect(route('admin.users.domains.registrations.show', [$user, $registration]))
            ->assertSessionHas('status', function (string $status) use ($user) {
                return str_contains($status, 'Rejected')
                    && str_contains($status, 'Rejection email sent to '.$user->email);
            });

        $this->assertSame(DomainRegistration::STATUS_REJECTED, $registration->fresh()->status);
    }

    public function test_approve_with_closely_related_updates_tools_and_includes_reference_in_mail(): void
    {
        [$user, $registration] = $this->seedManualPendingRegistration();
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('users.manage');

        $tool = \App\Models\UserTool::query()->create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'order_id' => $registration->order_id,
            'order_item_id' => $registration->order_item_id,
            'platform_product_id' => 1,
            'status' => \App\Enums\UserToolStatus::PendingSetup,
            'site_url' => null,
            'purchased_at' => now(),
        ]);

        $captured = null;
        $emails = \Mockery::mock(EmailService::class);
        $emails->shouldReceive('sendMailableHtml')
            ->once()
            ->withArgs(function ($to, $subject, $html, $text, $profile) use ($user, &$captured) {
                $captured = compact('to', 'subject', 'html', 'profile');

                return $to === $user->email && $profile === EmailProfile::Sales;
            })
            ->andReturn(\App\Services\Communications\Email\SendResult::ok('brevo', 'msg-approve'));
        $this->app->instance(EmailService::class, $emails);
        $this->app->forgetInstance(\App\Services\Communications\Email\OutboundMail::class);
        $this->app->forgetInstance(\App\Services\Notifications\Channels\MailChannel::class);
        $this->app->forgetInstance(\App\Services\Notifications\NotificationDispatcher::class);
        $this->app->forgetInstance(DomainRegistrationFulfillmentService::class);

        $this->actingAs($admin)
            ->post(route('admin.users.domains.registrations.approve', [$user, $registration]), [
                'provider_reference' => 'REG-7788',
                'closely_related_fqdn' => 'reject-me-hq.com',
            ])
            ->assertRedirect(route('admin.users.domains.registrations.show', [$user, $registration]))
            ->assertSessionHas('status', fn (string $s) => str_contains($s, 'reject-me-hq.com')
                && str_contains($s, 'reject-me.com'));

        $registration->refresh();
        $this->assertSame(DomainRegistration::STATUS_REGISTERED, $registration->status);
        $this->assertSame('reject-me-hq.com', $registration->fqdn);
        $this->assertSame('reject-me.com', $registration->unavailableFqdn());
        $this->assertSame('REG-7788', $registration->provider_reference);

        $tool->refresh();
        $this->assertSame('https://reject-me-hq.com', $tool->site_url);
        $this->assertSame('reject-me-hq.com', $tool->connectedDomainFqdn());

        $item = $registration->orderItem()->first();
        $this->assertSame('reject-me-hq.com', $item->options['domain_fqdn'] ?? null);

        $this->assertNotNull($captured);
        $this->assertStringContainsString('reject-me-hq.com', $captured['subject']);
        $this->assertStringContainsString('reject-me.com', $captured['html']);
        $this->assertStringContainsString('reject-me-hq.com', $captured['html']);
        $this->assertStringContainsString('REG-7788', $captured['html']);
    }

    public function test_admin_can_replace_registered_domain_everywhere(): void
    {
        [$user, $registration] = $this->seedManualPendingRegistration();
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('users.manage');

        $service = app(DomainRegistrationFulfillmentService::class);
        [$registered] = $service->markManualRegistered($registration, 'REF-1', null, $admin->id);
        $this->assertSame(DomainRegistration::STATUS_REGISTERED, $registered->status);

        $tool = \App\Models\UserTool::query()->create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'order_id' => $registered->order_id,
            'order_item_id' => $registered->order_item_id,
            'platform_product_id' => 1,
            'status' => \App\Enums\UserToolStatus::Active,
            'site_url' => 'https://reject-me.com',
            'purchased_at' => now(),
            'configured_at' => now(),
        ]);

        \App\Models\UserToolIntegration::query()->create([
            'user_tool_id' => $tool->id,
            'integration_id' => 'int-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8)),
            'client_id' => 'cid-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8)),
            'client_secret' => 'secret',
            'webhook_secret' => 'whsec',
            'connection_status' => 'ok',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.domains.registrations.replace', [$user, $registered]), [
                'fqdn' => 'brand-new.com',
                'note' => 'Customer requested rename.',
            ])
            ->assertRedirect(route('admin.users.domains.registrations.show', [$user, $registered]))
            ->assertSessionHas('status', fn (string $s) => str_contains($s, 'brand-new.com'));

        $registered->refresh();
        $this->assertSame('brand-new.com', $registered->fqdn);
        $this->assertSame(DomainRegistration::STATUS_REGISTERED, $registered->status);
        $this->assertSame('reject-me.com', $registered->provider_meta['admin_replaced_from_fqdn'] ?? null);

        $tool->refresh();
        $this->assertSame('https://brand-new.com', $tool->site_url);
        $this->assertSame('unchecked', $tool->integration?->fresh()->connection_status);

        $item = $registered->orderItem()->first();
        $this->assertSame('brand-new.com', $item->options['domain_fqdn'] ?? null);
    }

    public function test_email_from_name_never_uses_raw_address(): void
    {
        EmailIdentity::query()->updateOrCreate(
            ['profile' => 'sales'],
            [
                'from_name' => 'sales@7th-tradehub.online',
                'from_email' => 'sales@7th-tradehub.online',
                'enabled' => true,
                'is_default' => false,
            ]
        );

        $this->app->forgetInstance(EmailService::class);
        $service = $this->app->make(EmailService::class);
        $method = new \ReflectionMethod($service, 'resolveIdentity');
        $method->setAccessible(true);

        $email = new OutgoingEmail(
            to: [['email' => 'buyer@example.com']],
            subject: 'Order confirmed',
            profile: EmailProfile::Sales,
        );

        [$fromName, $fromEmail] = $method->invoke($service, $email);

        $this->assertSame('sales@7th-tradehub.online', $fromEmail);
        $this->assertNotSame('sales@7th-tradehub.online', $fromName);
        $this->assertNotEmpty($fromName);
        $this->assertFalse((bool) filter_var($fromName, FILTER_VALIDATE_EMAIL));
    }
}
