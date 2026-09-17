<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserAdminLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_member_user(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'New Member',
                'username' => 'new_member',
                'email' => 'new-member@example.com',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'email_verified' => '1',
                'kyc_level' => 0,
            ])
            ->assertRedirect();

        $member = User::query()->where('email', 'new-member@example.com')->first();
        $this->assertNotNull($member);
        $this->assertTrue($member->hasRole('user'));
        $this->assertFalse($member->hasRole('admin'));
        $this->assertNotNull($member->email_verified_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.created']);
    }

    public function test_admin_can_send_password_reset_link(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now()]);
        $member->assignRole('user');

        $this->actingAs($admin)
            ->post(route('admin.users.password-reset', $member))
            ->assertRedirect();

        Notification::assertSentTo($member, ResetPassword::class);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.password_reset_link_sent']);
    }

    public function test_admin_can_update_email_with_mixed_case(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create([
            'email' => 'old@example.com',
            'email_verified_at' => now(),
            'username' => 'member_one',
        ]);
        $member->assignRole('user');

        $this->actingAs($admin)
            ->put(route('admin.users.update', $member), [
                'name' => $member->name,
                'username' => $member->username,
                'email' => 'New.Email@Example.COM',
                'phone' => $member->phone,
                'country' => $member->country,
                'bio' => $member->bio,
                'kyc_level' => $member->kyc_level,
            ])
            ->assertRedirect(route('admin.users.show', $member));

        $member->refresh();
        $this->assertSame('new.email@example.com', $member->email);
        $this->assertNull($member->email_verified_at);
    }

    public function test_admin_can_manually_set_user_password(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create([
            'email_verified_at' => now(),
            'username' => 'member_pw',
            'password' => 'OldPassword1!',
        ]);
        $member->assignRole('user');

        $this->actingAs($admin)
            ->put(route('admin.users.update', $member), [
                'name' => $member->name,
                'username' => $member->username,
                'email' => $member->email,
                'phone' => $member->phone,
                'country' => $member->country,
                'bio' => $member->bio,
                'kyc_level' => $member->kyc_level,
                'password' => 'NewPassword1!',
                'password_confirmation' => 'NewPassword1!',
            ])
            ->assertRedirect(route('admin.users.show', $member));

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewPassword1!', $member->fresh()->password));
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.password_set_by_admin']);
    }

    public function test_admin_can_verify_and_unverify_email(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => null]);
        $member->assignRole('user');

        $this->actingAs($admin)
            ->post(route('admin.users.verify-email', $member))
            ->assertRedirect();

        $this->assertNotNull($member->fresh()->email_verified_at);

        $this->actingAs($admin)
            ->post(route('admin.users.unverify-email', $member))
            ->assertRedirect();

        $this->assertNull($member->fresh()->email_verified_at);
    }

    public function test_impersonation_bypasses_email_verification(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->unverified()->create(['name' => 'Unverified Member']);
        $member->assignRole('user');

        $this->actingAs($admin)
            ->post(route('admin.users.impersonate', $member))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($member);
        $this->assertTrue(session('impersonating'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('You are impersonating', false)
            ->assertDontSee('verify-email', false);
    }

    public function test_impersonation_start_and_leave(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now(), 'name' => 'Impersonated Member']);
        $member->assignRole('user');

        $this->actingAs($admin)
            ->post(route('admin.users.impersonate', $member))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($member);
        $this->assertTrue(session('impersonating'));
        $this->assertSame($admin->id, session('impersonator_id'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('You are impersonating', false)
            ->assertSee('Impersonated Member');

        $this->post(route('impersonation.leave'))
            ->assertRedirect(route('admin.users.show', $member));

        $this->assertAuthenticatedAs($admin);
        $this->assertFalse((bool) session('impersonating'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.impersonation.started']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.impersonation.stopped']);
    }

    public function test_cannot_impersonate_admin_or_self_or_suspended(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $otherAdmin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $suspended = User::factory()->create([
            'email_verified_at' => now(),
            'is_suspended' => true,
        ]);
        $suspended->assignRole('user');

        $this->actingAs($admin)
            ->post(route('admin.users.impersonate', $otherAdmin))
            ->assertRedirect();

        $this->assertAuthenticatedAs($admin);

        $this->actingAs($admin)
            ->from(route('admin.users'))
            ->post(route('admin.users.impersonate', $admin))
            ->assertRedirect();

        $this->actingAs($admin)
            ->from(route('admin.users.show', $suspended))
            ->post(route('admin.users.impersonate', $suspended))
            ->assertRedirect();

        $this->assertFalse((bool) session('impersonating'));
    }
}
