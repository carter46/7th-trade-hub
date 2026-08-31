<?php

namespace Tests\Feature\Domains;

use App\Models\DomainConnection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\Domains\DomainConnectionService;
use App\Services\Domains\DomainDnsLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainConnectFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['domains.default_nameservers' => ['ns1.platform.test', 'ns2.platform.test']]);
    }

    private function fakeDns(callable $resolver): void
    {
        $this->app->instance(DomainDnsLookupService::class, new DomainDnsLookupService($resolver));
        $this->app->forgetInstance(DomainConnectionService::class);
        $this->app->forgetInstance(\App\Services\Domains\DomainCheckoutValidator::class);
    }

    public function test_connect_scan_returns_safe_payload(): void
    {
        $this->fakeDns(fn () => [
            ['target' => 'ns1.oldhost.test'],
            ['target' => 'ns2.oldhost.test'],
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.services.domain-connect-scan'), [
                'domain_fqdn' => 'example.com',
            ]);

        $response->assertOk()
            ->assertJson([
                'fqdn' => 'example.com',
                'registered' => true,
                'status' => 'active',
                'already_connected' => false,
            ])
            ->assertJsonPath('nameservers.0', 'ns1.oldhost.test')
            ->assertJsonPath('required_nameservers.0', 'ns1.platform.test');

        $payload = $response->json();
        $this->assertArrayNotHasKey('provider_key', $payload);
        $this->assertArrayNotHasKey('provider_cost', $payload);
    }

    public function test_connect_scan_rejects_unresolvable_domain(): void
    {
        $this->fakeDns(fn () => []);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.services.domain-connect-scan'), [
                'domain_fqdn' => 'missing.com',
            ]);

        $response->assertOk()
            ->assertJson([
                'registered' => false,
                'already_connected' => false,
            ]);
        $this->assertNotEmpty($response->json('message'));
    }

    public function test_already_connected_domain_blocked_for_other_user(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $other = User::factory()->create(['email_verified_at' => now()]);
        $other->assignRole('user');

        $order = Order::query()->create([
            'source' => 'platform',
            'user_id' => $owner->id,
            'reference' => 'PLT-TEST1',
            'amount' => 100,
            'total_amount' => 100,
            'status' => 'paid',
            'payment_method' => 'wallet',
        ]);
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'item_type' => 'platform_product',
            'item_id' => 1,
            'quantity' => 1,
            'unit_price' => 100,
            'line_total' => 100,
            'options' => ['domain_mode' => 'connect', 'domain_fqdn' => 'taken.com'],
        ]);

        DomainConnection::query()->create([
            'user_id' => $owner->id,
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'fqdn' => 'taken.com',
            'claim_key' => 'taken.com',
            'nameservers_at_scan' => ['ns1.old.test'],
            'required_nameservers' => ['ns1.platform.test', 'ns2.platform.test'],
            'verification_status' => DomainConnection::STATUS_PENDING,
            'acknowledged_at' => now(),
        ]);

        $this->app->instance(
            DomainDnsLookupService::class,
            new DomainDnsLookupService(fn () => [
                ['target' => 'ns1.old.test'],
                ['target' => 'ns2.old.test'],
            ]),
        );
        $this->app->forgetInstance(DomainConnectionService::class);
        $this->app->forgetInstance(\App\Services\Domains\DomainCheckoutValidator::class);

        $response = $this->actingAs($other)
            ->postJson(route('dashboard.services.domain-connect-scan'), [
                'domain_fqdn' => 'taken.com',
            ]);

        $response->assertOk()->assertJson([
            'already_connected' => true,
        ]);
        $this->assertStringContainsString('already connected', strtolower((string) $response->json('message')));
    }

    public function test_check_status_verifies_when_platform_ns_present(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $order = Order::query()->create([
            'source' => 'platform',
            'user_id' => $user->id,
            'reference' => 'PLT-TEST2',
            'amount' => 100,
            'total_amount' => 100,
            'status' => 'paid',
            'payment_method' => 'wallet',
        ]);
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'item_type' => 'platform_product',
            'item_id' => 1,
            'quantity' => 1,
            'unit_price' => 100,
            'line_total' => 100,
            'options' => [],
        ]);

        $connection = DomainConnection::query()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'fqdn' => 'verifyme.com',
            'claim_key' => 'verifyme.com',
            'nameservers_at_scan' => ['ns1.old.test', 'ns2.old.test'],
            'nameservers_last_seen' => ['ns1.old.test', 'ns2.old.test'],
            'required_nameservers' => ['ns1.platform.test', 'ns2.platform.test'],
            'verification_status' => DomainConnection::STATUS_PENDING,
            'acknowledged_at' => now(),
        ]);

        $this->fakeDns(fn () => [
            ['target' => 'ns1.platform.test'],
            ['target' => 'ns2.platform.test'],
            ['target' => 'ns3.extra.test'],
        ]);

        $result = app(DomainConnectionService::class)->checkStatus($connection);

        $this->assertTrue($result['ok']);
        $this->assertSame(DomainConnection::STATUS_VERIFIED, $result['connection']->verification_status);
    }

    public function test_check_status_stays_pending_when_ns_mismatch(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $order = Order::query()->create([
            'source' => 'platform',
            'user_id' => $user->id,
            'reference' => 'PLT-TEST3',
            'amount' => 100,
            'total_amount' => 100,
            'status' => 'paid',
            'payment_method' => 'wallet',
        ]);
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'item_type' => 'platform_product',
            'item_id' => 1,
            'quantity' => 1,
            'unit_price' => 100,
            'line_total' => 100,
            'options' => [],
        ]);

        $connection = DomainConnection::query()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'fqdn' => 'stillold.com',
            'claim_key' => 'stillold.com',
            'nameservers_at_scan' => ['ns1.old.test', 'ns2.old.test'],
            'required_nameservers' => ['ns1.platform.test', 'ns2.platform.test'],
            'verification_status' => DomainConnection::STATUS_PENDING,
            'acknowledged_at' => now(),
        ]);

        $this->fakeDns(fn () => [
            ['target' => 'ns1.old.test'],
            ['target' => 'ns2.old.test'],
        ]);

        $result = app(DomainConnectionService::class)->checkStatus($connection);

        $this->assertFalse($result['ok']);
        $this->assertSame(DomainConnection::STATUS_PENDING, $result['connection']->verification_status);
    }
}
