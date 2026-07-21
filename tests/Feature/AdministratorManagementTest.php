<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministratorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrators_index_requires_permission(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        // admins.manage is direct-only and is not on the admin role.

        $this->actingAs($admin)
            ->get(route('admin.administrators'))
            ->assertForbidden();
    }

    public function test_can_create_administrator_with_password(): void
    {
        $actor = User::factory()->admin()->create(['email_verified_at' => now()]);

        $this->actingAs($actor)
            ->post(route('admin.administrators.store'), [
                'name' => 'New Admin',
                'username' => 'new_admin',
                'email' => 'new-admin@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'grant_admins_manage' => '1',
            ])
            ->assertRedirect(route('admin.administrators'));

        $created = User::where('email', 'new-admin@example.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole('admin'));
        $this->assertTrue($created->can('admins.manage'));
    }

    public function test_cannot_suspend_self(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        $this->actingAs($admin)
            ->post(route('admin.administrators.suspend', $admin))
            ->assertRedirect();

        $this->assertFalse($admin->fresh()->is_suspended);
    }

    public function test_users_page_cannot_assign_roles(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now()]);
        $member->assignRole('user');

        $this->actingAs($admin)
            ->post(route('admin.users.role', $member), ['role' => 'admin'])
            ->assertForbidden();
    }

    public function test_cannot_delete_administrators_via_users_destroy(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $other = User::factory()->admin()->create([
            'email_verified_at' => now(),
            'is_suspended' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $other))
            ->assertNotFound();

        $this->assertNull($other->fresh()->anonymized_at);
    }
}
