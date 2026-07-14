<?php

namespace Tests\Feature\Admin;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reply_and_close_ticket(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'category' => 'wallet',
            'subject' => 'Balance issue',
            'body' => 'My balance looks wrong.',
            'status' => 'open',
        ]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Balance issue');

        $this->actingAs($admin)
            ->post(route('admin.tickets.reply', $ticket), ['body' => 'We are reviewing your wallet.'])
            ->assertRedirect();

        $reply = SupportTicketReply::where('support_ticket_id', $ticket->id)->first();
        $this->assertNotNull($reply);
        $this->assertTrue($reply->is_staff);

        $this->actingAs($admin)
            ->post(route('admin.tickets.status', $ticket), ['status' => 'closed'])
            ->assertRedirect();

        $this->assertSame('closed', $ticket->fresh()->status);
    }
}
