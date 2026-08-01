<?php

namespace Tests\Feature\Dashboard;

use App\Models\Escrow;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EscrowConversationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_compose_and_direct_store_are_blocked(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('dashboard.messages.create'))
            ->assertRedirect(route('dashboard.messages'));

        $this->actingAs($user)
            ->post(route('dashboard.messages.store'), [
                'to_email' => 'someone@example.com',
                'subject' => 'Hi',
                'body' => 'Hello',
            ])
            ->assertRedirect(route('dashboard.messages'));
    }

    public function test_index_lists_active_escrow_threads_only(): void
    {
        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $buyer->assignRole('user');
        $seller = User::factory()->create(['email_verified_at' => now()]);
        $seller->assignRole('user');

        $listing = Listing::factory()->published()->create(['user_id' => $seller->id]);
        $buyerWallet = Wallet::factory()->create(['user_id' => $buyer->id]);
        $sellerWallet = Wallet::factory()->create(['user_id' => $seller->id]);

        $active = Order::factory()->create([
            'user_id' => $buyer->id,
            'listing_id' => $listing->id,
            'source' => 'marketplace',
            'status' => 'processing',
            'reference' => 'ACT-ESCROW-1',
        ]);
        Escrow::factory()->create([
            'order_id' => $active->id,
            'buyer_wallet_id' => $buyerWallet->id,
            'seller_wallet_id' => $sellerWallet->id,
            'status' => 'locked',
            'amount' => $active->amount,
        ]);

        $closed = Order::factory()->create([
            'user_id' => $buyer->id,
            'listing_id' => $listing->id,
            'source' => 'marketplace',
            'status' => 'completed',
            'reference' => 'CLOSED-ESCROW-1',
        ]);
        Escrow::factory()->create([
            'order_id' => $closed->id,
            'buyer_wallet_id' => $buyerWallet->id,
            'seller_wallet_id' => $sellerWallet->id,
            'status' => 'released',
            'amount' => $closed->amount,
        ]);

        $this->actingAs($buyer)
            ->get(route('dashboard.messages'))
            ->assertOk()
            ->assertSee('Escrow Conversations')
            ->assertSee('ACT-ESCROW-1')
            ->assertDontSee('CLOSED-ESCROW-1');

        $this->actingAs($buyer)
            ->get(route('dashboard.messages', ['status' => 'closed']))
            ->assertOk()
            ->assertSee('CLOSED-ESCROW-1')
            ->assertDontSee('ACT-ESCROW-1');
    }

    public function test_reply_blocked_when_escrow_closed(): void
    {
        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $buyer->assignRole('user');
        $seller = User::factory()->create(['email_verified_at' => now()]);
        $seller->assignRole('user');

        $listing = Listing::factory()->published()->create(['user_id' => $seller->id]);
        $buyerWallet = Wallet::factory()->create(['user_id' => $buyer->id]);
        $sellerWallet = Wallet::factory()->create(['user_id' => $seller->id]);

        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'listing_id' => $listing->id,
            'source' => 'marketplace',
            'status' => 'completed',
        ]);
        Escrow::factory()->create([
            'order_id' => $order->id,
            'buyer_wallet_id' => $buyerWallet->id,
            'seller_wallet_id' => $sellerWallet->id,
            'status' => 'released',
            'amount' => $order->amount,
        ]);

        $this->actingAs($buyer)
            ->post(route('dashboard.messages.order.reply', $order), ['body' => 'Still here?'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, Message::where('order_id', $order->id)->count());
    }
}
