<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_renders_the_username(): void
    {
        $user = User::factory()->create(['username' => 'jane_member']);

        $this->get(route('user.profile', $user->username))
            ->assertOk()
            ->assertSee('jane_member')
            ->assertDontSee('Marketplace');
    }

    public function test_unknown_username_returns_not_found(): void
    {
        $this->get('/u/nobody-here')->assertNotFound();
    }
}
