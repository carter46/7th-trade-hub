<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneNotificationsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_prunes_old_read_notifications(): void
    {
        $user = User::factory()->create();

        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'test',
            'title' => 'Old read',
            'read_at' => now()->subDays(100),
        ]);

        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'test',
            'title' => 'Recent unread',
        ]);

        $this->artisan('app:prune-notifications', ['--days' => 90])
            ->assertSuccessful();

        $this->assertDatabaseMissing('user_notifications', ['title' => 'Old read']);
        $this->assertDatabaseHas('user_notifications', ['title' => 'Recent unread']);
    }
}
