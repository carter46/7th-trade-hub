<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\Communications\Email\EmailService;
use App\Services\Communications\Email\SendResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Mockery;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $emails = Mockery::mock(EmailService::class);
        $emails->shouldReceive('sendMailableHtml')
            ->once()
            ->andReturn(SendResult::ok('laravel_mail'));
        $this->app->instance(EmailService::class, $emails);

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHasNoErrors();
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->get('/reset-password/'.$token.'?email='.urlencode($user->email))
            ->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));
    }
}
