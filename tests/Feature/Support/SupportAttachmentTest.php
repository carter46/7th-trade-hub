<?php

namespace Tests\Feature\Support;

use App\Models\SupportAttachment;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupportAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_attach_image_when_creating_ticket(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $file = UploadedFile::fake()->image('screenshot.png', 200, 200);

        $this->actingAs($user)
            ->post(route('dashboard.support.store'), [
                'category' => 'technical',
                'subject' => 'Broken checkout',
                'body' => 'See screenshot',
                'attachments' => [$file],
            ])
            ->assertRedirect();

        $ticket = SupportTicket::query()->first();
        $this->assertNotNull($ticket);
        $this->assertSame(1, $ticket->attachments()->count());
        $attachment = $ticket->attachments()->first();
        $this->assertTrue($attachment->expires_at->greaterThan(now()->addHours(70)));
        Storage::disk('local')->assertExists($attachment->path);
    }

    public function test_prune_command_deletes_expired_files(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $ticket = SupportTicket::factory()->create(['user_id' => $user->id]);
        $path = 'support-evidence/'.$ticket->id.'/old.txt';
        Storage::disk('local')->put($path, 'evidence');

        SupportAttachment::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => 'old.txt',
            'mime' => 'text/plain',
            'size' => 8,
            'expires_at' => now()->subHour(),
        ]);

        $this->artisan('support:prune-attachments')->assertSuccessful();

        $this->assertSame(0, SupportAttachment::count());
        Storage::disk('local')->assertMissing($path);
    }

    public function test_support_index_shows_contact_quick_actions(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('dashboard.support.index'))
            ->assertOk()
            ->assertSee('Support Center')
            ->assertSee('Contact Us')
            ->assertSee('Help Center')
            ->assertSee('Open Ticket');
    }
}
