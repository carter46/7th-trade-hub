<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_excludes_administrators(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now(), 'name' => 'Member Only']);
        $member->assignRole('user');

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Member Only')
            ->assertDontSee($admin->email);
    }

    public function test_suspended_tab_lists_suspended_users(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $active = User::factory()->create(['email_verified_at' => now(), 'name' => 'Active Member']);
        $active->assignRole('user');
        $suspended = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'Suspended Member',
            'is_suspended' => true,
        ]);
        $suspended->assignRole('user');

        $this->actingAs($admin)
            ->get(route('admin.users', ['status' => 'suspended']))
            ->assertOk()
            ->assertSee('Suspended Member')
            ->assertDontSee('Active Member');
    }

    public function test_anonymize_scrubs_pii_and_keeps_record(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create([
            'email_verified_at' => now(),
            'is_suspended' => true,
            'email' => 'keep-history@example.com',
            'username' => 'keep_history',
        ]);
        $member->assignRole('user');
        $memberId = $member->id;

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $member))
            ->assertRedirect(route('admin.users', ['status' => 'suspended']));

        $member->refresh();
        $this->assertNotNull($member->anonymized_at);
        $this->assertSame('Deleted User', $member->name);
        $this->assertSame('deleted_'.$memberId, $member->username);
        $this->assertSame('deleted+'.$memberId.'@invalid.local', $member->email);
        $this->assertDatabaseHas('users', ['id' => $memberId]);
    }

    public function test_user_workspace_overview_loads(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now()]);
        $member->assignRole('user');

        $this->actingAs($admin)
            ->get(route('admin.users.show', $member))
            ->assertOk()
            ->assertSee($member->name)
            ->assertSee('Overview');
    }
}
