<?php

namespace Tests\Feature\Domains;

use App\Models\DomainProvider;
use App\Models\DomainRegistration;
use App\Models\Order;
use App\Models\User;
use App\Services\Domains\DomainNameserverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DomainNameserverUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function registeredDomain(User $owner): DomainRegistration
    {
        DomainProvider::query()->where('key', 'namecom')->update([
            'enabled' => true,
            'sandbox' => true,
            'credentials' => ['username' => 'user', 'api_token' => 'token'],
        ]);

        $order = Order::query()->create([
            'source' => 'platform',
            'user_id' => $owner->id,
            'reference' => 'PLT-NS01',
            'amount' => 100,
            'total_amount' => 100,
            'status' => 'paid',
            'payment_method' => 'wallet',
        ]);

        return DomainRegistration::query()->create([
            'order_id' => $order->id,
            'fqdn' => 'update-test.com',
            'provider_key' => 'namecom',
            'status' => DomainRegistration::STATUS_REGISTERED,
            'nameservers' => ['ns1.old.test', 'ns2.old.test'],
            'nameservers_updated_at' => now()->subDay(),
            'registered_at' => now()->subDay(),
        ]);
    }

    public function test_successful_update_persists_nameservers_after_provider_confirms(): void
    {
        $user = User::factory()->create();
        $registration = $this->registeredDomain($user);

        Http::fake([
            'https://api.dev.name.com/core/v1/domains/update-test.com:setNameservers' => Http::response([
                'nameservers' => ['ns1.cloudflare.test', 'ns2.cloudflare.test'],
            ]),
        ]);

        $updated = app(DomainNameserverService::class)->updateForCustomer($registration, [
            'ns1.cloudflare.test',
            'ns2.cloudflare.test',
        ], $user);

        $this->assertSame(['ns1.cloudflare.test', 'ns2.cloudflare.test'], $updated);

        $registration->refresh();
        $this->assertSame(['ns1.cloudflare.test', 'ns2.cloudflare.test'], $registration->nameservers);
        $this->assertNotNull($registration->nameservers_updated_at);
    }

    public function test_failed_provider_update_leaves_database_unchanged(): void
    {
        $user = User::factory()->create();
        $registration = $this->registeredDomain($user);
        $originalUpdatedAt = $registration->nameservers_updated_at;

        Http::fake([
            'https://api.dev.name.com/core/v1/domains/update-test.com:setNameservers' => Http::response(['message' => 'failed'], 500),
        ]);

        try {
            app(DomainNameserverService::class)->updateForCustomer($registration, [
                'ns1.cloudflare.test',
                'ns2.cloudflare.test',
            ], $user);
            $this->fail('Expected exception was not thrown.');
        } catch (\Throwable) {
            // expected
        }

        $registration->refresh();
        $this->assertSame(['ns1.old.test', 'ns2.old.test'], $registration->nameservers);
        $this->assertTrue($registration->nameservers_updated_at->equalTo($originalUpdatedAt));
    }

    public function test_customer_can_update_via_dashboard_route(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        $registration = $this->registeredDomain($user);

        Http::fake([
            'https://api.dev.name.com/core/v1/domains/update-test.com:setNameservers' => Http::response([
                'nameservers' => ['ns1.host.test', 'ns2.host.test'],
            ]),
        ]);

        $this->actingAs($user)
            ->put(route('dashboard.my-domains.nameservers.update', $registration), [
                'nameserver_1' => 'ns1.host.test',
                'nameserver_2' => 'ns2.host.test',
            ])
            ->assertRedirect(route('dashboard.my-domains.show', $registration));

        $registration->refresh();
        $this->assertSame(['ns1.host.test', 'ns2.host.test'], $registration->nameservers);
    }

    public function test_other_user_cannot_manage_domain(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create(['email_verified_at' => now()]);
        $other->assignRole('user');
        $registration = $this->registeredDomain($owner);

        $this->actingAs($other)
            ->get(route('dashboard.my-domains.show', $registration))
            ->assertNotFound();
    }
}
