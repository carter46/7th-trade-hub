<?php

namespace Tests\Feature\Dashboard;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersSplitTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketplace_and_service_orders_are_filtered_and_cta_stays_in_dashboard(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        Order::factory()->create([
            'user_id' => $user->id,
            'source' => 'marketplace',
            'reference' => 'MKT-ORDER-1',
        ]);
        Order::factory()->platform()->create([
            'user_id' => $user->id,
            'reference' => 'SVC-ORDER-1',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.orders'))
            ->assertOk()
            ->assertSee('Marketplace orders')
            ->assertSee('MKT-ORDER-1')
            ->assertDontSee('SVC-ORDER-1');

        $this->actingAs($user)
            ->get(route('dashboard.service-orders'))
            ->assertOk()
            ->assertSee('Service orders')
            ->assertSee('SVC-ORDER-1')
            ->assertDontSee('MKT-ORDER-1');

        $emptyUser = User::factory()->create(['email_verified_at' => now()]);
        $emptyUser->assignRole('user');

        $this->actingAs($emptyUser)
            ->get(route('dashboard.service-orders'))
            ->assertOk()
            ->assertSee('Browse services')
            ->assertSee(url('/dashboard/services'), false);
    }
}
