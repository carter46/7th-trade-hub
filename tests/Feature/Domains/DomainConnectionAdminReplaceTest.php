<?php

namespace Tests\Feature\Domains;

use App\Enums\UserToolStatus;
use App\Models\DomainConnection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\UserTool;
use App\Models\UserToolIntegration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DomainConnectionAdminReplaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['domains.default_nameservers' => ['ns1.platform.test', 'ns2.platform.test']]);
    }

    /**
     * @return array{0: User, 1: User, 2: DomainConnection, 3: UserTool, 4: OrderItem}
     */
    private function seedVerifiedConnection(string $fqdn = 'old-site.com', string $reference = 'PLT-CONN1'): array
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('users.manage');

        $order = Order::query()->create([
            'source' => 'platform',
            'user_id' => $user->id,
            'reference' => $reference,
            'amount' => 27000,
            'total_amount' => 27000,
            'status' => 'paid',
            'payment_method' => 'wallet',
        ]);

        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'item_type' => 'platform_product',
            'item_id' => 1,
            'quantity' => 1,
            'unit_price' => 27000,
            'line_total' => 27000,
            'options' => [
                'domain_mode' => 'connect',
                'domain_fqdn' => $fqdn,
                'domain_name' => $fqdn,
                'domain_tld' => 'com',
            ],
        ]);

        $tool = UserTool::query()->create([
            'public_id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'platform_product_id' => 1,
            'status' => UserToolStatus::Active,
            'site_url' => 'https://'.$fqdn,
            'admin_login_url' => 'https://'.$fqdn.'/admin/login?x=1',
            'purchased_at' => now(),
            'configured_at' => now(),
        ]);

        UserToolIntegration::query()->create([
            'user_tool_id' => $tool->id,
            'integration_id' => 'int-'.Str::lower(Str::random(8)),
            'client_id' => 'cid-'.Str::lower(Str::random(8)),
            'client_secret' => 'secret',
            'webhook_secret' => 'whsec',
            'connection_status' => 'ok',
        ]);

        $connection = DomainConnection::query()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'user_tool_id' => $tool->id,
            'fqdn' => $fqdn,
            'claim_key' => $fqdn,
            'nameservers_at_scan' => ['ns1.old.test'],
            'nameservers_last_seen' => ['ns1.platform.test', 'ns2.platform.test'],
            'required_nameservers' => ['ns1.platform.test', 'ns2.platform.test'],
            'verification_status' => DomainConnection::STATUS_VERIFIED,
            'acknowledged_at' => now(),
            'verified_at' => now(),
        ]);

        return [$user, $admin, $connection, $tool, $item];
    }

    public function test_admin_can_replace_verified_connected_domain(): void
    {
        [$user, $admin, $connection, $tool, $item] = $this->seedVerifiedConnection();

        $this->actingAs($admin)
            ->get(route('admin.users.domains.connections.show', [$user, $connection]))
            ->assertOk()
            ->assertSee('Replace domain');

        $this->actingAs($admin)
            ->post(route('admin.users.domains.connections.replace', [$user, $connection]), [
                'fqdn' => 'new-site.com',
                'note' => 'Customer moved domains.',
            ])
            ->assertRedirect(route('admin.users.domains.connections.show', [$user, $connection]))
            ->assertSessionHas('status', fn (string $s) => str_contains($s, 'old-site.com') && str_contains($s, 'new-site.com'));

        $connection->refresh();
        $this->assertSame('new-site.com', $connection->fqdn);
        $this->assertSame('new-site.com', $connection->claim_key);
        $this->assertSame(DomainConnection::STATUS_PENDING, $connection->verification_status);
        $this->assertNull($connection->verified_at);

        $item->refresh();
        $this->assertSame('new-site.com', $item->options['domain_fqdn']);
        $this->assertSame('new-site.com', $item->options['domain_name']);
        $this->assertSame('connect', $item->options['domain_mode']);

        $tool->refresh();
        $this->assertSame('https://new-site.com', $tool->site_url);
        $this->assertSame('https://new-site.com/admin/login?x=1', $tool->admin_login_url);
        $this->assertSame('unchecked', $tool->integration?->connection_status);
        $this->assertSame('new-site.com', $tool->connectedDomainFqdn());

        $this->actingAs($admin)
            ->post(route('admin.users.domains.connections.approve', [$user, $connection]))
            ->assertRedirect();
        $this->assertSame(DomainConnection::STATUS_VERIFIED, $connection->fresh()->verification_status);
    }

    public function test_admin_can_replace_with_subdomain_and_keep_it_verified(): void
    {
        [$user, $admin, $connection, $tool, $item] = $this->seedVerifiedConnection();

        $this->actingAs($admin)
            ->post(route('admin.users.domains.connections.replace', [$user, $connection]), [
                'fqdn' => 'https://Shop.Example-Brand.co.uk/',
                'mark_verified' => '1',
            ])
            ->assertRedirect(route('admin.users.domains.connections.show', [$user, $connection]))
            ->assertSessionHas('status', fn (string $s) => str_contains($s, 'Marked as verified'));

        $connection->refresh();
        $this->assertSame('shop.example-brand.co.uk', $connection->fqdn);
        $this->assertSame(DomainConnection::STATUS_VERIFIED, $connection->verification_status);
        $this->assertNotNull($connection->verified_at);

        $this->assertSame('shop.example-brand.co.uk', $item->fresh()->options['domain_fqdn']);
        $this->assertSame('https://shop.example-brand.co.uk', $tool->fresh()->site_url);

        // External DNS: the customer's NS check must not downgrade an admin-verified domain.
        $this->app->instance(
            \App\Services\Domains\DomainDnsLookupService::class,
            new \App\Services\Domains\DomainDnsLookupService(fn () => []),
        );
        $this->app->forgetInstance(\App\Services\Domains\DomainConnectionService::class);

        $result = app(\App\Services\Domains\DomainConnectionService::class)->checkStatus($connection);
        $this->assertTrue($result['ok']);
        $this->assertSame(DomainConnection::STATUS_VERIFIED, $connection->fresh()->verification_status);
    }

    public function test_replace_rejects_domain_connected_elsewhere(): void
    {
        [$user, $admin, $connection] = $this->seedVerifiedConnection('first-site.com', 'PLT-CONN1');
        $this->seedVerifiedConnection('taken-site.com', 'PLT-CONN2');

        $this->actingAs($admin)
            ->from(route('admin.users.domains.connections.show', [$user, $connection]))
            ->post(route('admin.users.domains.connections.replace', [$user, $connection]), [
                'fqdn' => 'taken-site.com',
            ])
            ->assertRedirect(route('admin.users.domains.connections.show', [$user, $connection]))
            ->assertSessionHas('error');

        $this->assertSame('first-site.com', $connection->fresh()->fqdn);
        $this->assertSame(DomainConnection::STATUS_VERIFIED, $connection->fresh()->verification_status);
    }

    public function test_replace_rejects_same_domain(): void
    {
        [$user, $admin, $connection] = $this->seedVerifiedConnection();

        $this->actingAs($admin)
            ->post(route('admin.users.domains.connections.replace', [$user, $connection]), [
                'fqdn' => 'OLD-SITE.com',
            ])
            ->assertSessionHas('error');

        $this->assertSame(DomainConnection::STATUS_VERIFIED, $connection->fresh()->verification_status);
    }
}
