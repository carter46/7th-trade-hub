<?php

namespace Tests\Feature\Domains;

use App\Models\DomainRegistration;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyDomainsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_only_their_domains(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        $other = User::factory()->create();

        $order = Order::query()->create([
            'source' => 'platform',
            'user_id' => $user->id,
            'reference' => 'PLT-MD1',
            'amount' => 100,
            'total_amount' => 100,
            'status' => 'paid',
            'payment_method' => 'wallet',
        ]);

        $mine = DomainRegistration::query()->create([
            'order_id' => $order->id,
            'fqdn' => 'mine.example.com',
            'provider_key' => 'namecom',
            'status' => DomainRegistration::STATUS_REGISTERED,
            'nameservers' => ['ns1.example.com', 'ns2.example.com'],
            'registered_at' => now(),
        ]);

        $otherOrder = Order::query()->create([
            'source' => 'platform',
            'user_id' => $other->id,
            'reference' => 'PLT-MD2',
            'amount' => 100,
            'total_amount' => 100,
            'status' => 'paid',
            'payment_method' => 'wallet',
        ]);

        DomainRegistration::query()->create([
            'order_id' => $otherOrder->id,
            'fqdn' => 'not-mine.example.com',
            'provider_key' => 'namecom',
            'status' => DomainRegistration::STATUS_REGISTERED,
            'registered_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.my-tools.domains'))
            ->assertOk()
            ->assertSee('mine.example.com')
            ->assertDontSee('not-mine.example.com');
    }

    public function test_legacy_my_domains_index_redirects_to_tools_domains_tab(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('dashboard.my-domains'))
            ->assertRedirect(route('dashboard.my-tools.domains'));
    }
}
