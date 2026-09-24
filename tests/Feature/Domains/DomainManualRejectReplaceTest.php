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
                    && in_array('mail', $channels, true);
            });
        $dispatcher->shouldReceive('notifyAdmins')->once();
        $dispatcher->shouldReceive('notifyUser')
            ->once()
            ->withArgs(function ($notifiable, $message, $channels) use ($user) {
                return (int) $notifiable->id === (int) $user->id
                    && $message->type === 'order.domain_approved'
                    && in_array('mail', $channels, true);
            });

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

        $service = app(EmailService::class);
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
