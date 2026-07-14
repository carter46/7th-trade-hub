<?php

namespace Tests\Feature\Marketplace;

use App\Models\Listing;
use App\Models\Message;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_send_message_and_recipient_gets_notification(): void
    {
        $sender = User::factory()->create(['email_verified_at' => now()]);
        $sender->assignRole('user');
        $recipient = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($sender)
            ->post(route('dashboard.messages.store'), [
                'to_email' => $recipient->email,
                'subject' => 'Hello there',
                'body' => 'Interested in your service.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'from_user_id' => $sender->id,
            'to_user_id' => $recipient->id,
            'subject' => 'Hello there',
        ]);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $recipient->id,
            'type' => 'message',
        ]);
    }
}
