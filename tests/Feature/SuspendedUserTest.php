<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuspendedUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspended_user_is_logged_out_on_next_request(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_suspended' => true,
        ]);
        $user->assignRole('user');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_suspended_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_suspended' => true,
            'password' => 'password',
        ]);
        $user->assignRole('user');

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_toggle_user_suspension(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $user = User::factory()->create(['email_verified_at' => now(), 'is_suspended' => false]);
        $user->assignRole('user');

        $this->actingAs($admin)
            ->post(route('admin.users.suspend', $user))
            ->assertRedirect();

        $this->assertTrue($user->fresh()->is_suspended);

        $this->actingAs($admin)
            ->post(route('admin.users.restore', $user))
            ->assertRedirect();

        $this->assertFalse($user->fresh()->is_suspended);
    }
}
