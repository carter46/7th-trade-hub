<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_another_users_support_ticket(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $other = User::factory()->create(['email_verified_at' => now()]);

        $ticket = SupportTicket::create([
            'user_id' => $owner->id,
            'category' => 'wallet',
            'subject' => 'Test',
            'body' => 'Help',
            'status' => 'open',
        ]);

        $this->actingAs($other)
            ->get(route('dashboard.support.show', $ticket))
            ->assertForbidden();
    }

    public function test_user_cannot_open_another_users_manual_payment_page(): void
    {
        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $other = User::factory()->create(['email_verified_at' => now()]);

        $order = Order::create([
            'user_id' => $buyer->id,
            'reference' => 'PLT-TEST-1',
            'source' => 'platform',
            'amount' => 100,
            'status' => 'pending',
            'payment_method' => Order::PAYMENT_MANUAL_BANK_TRANSFER,
        ]);

        $this->actingAs($other)
            ->get(route('dashboard.orders.manual-payment', $order))
            ->assertForbidden();
    }
}
