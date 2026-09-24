<?php

namespace Tests\Feature\Communications;

use App\Services\Communications\Email\EmailProfile;
use App\Services\Communications\Email\EmailService;
use App\Services\Communications\Email\OutboundMail;
use App\Services\Communications\Email\SendResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class OutboundMailFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_falls_back_to_noreply_when_primary_profile_fails(): void
    {
        $emails = Mockery::mock(EmailService::class);
        $emails->shouldReceive('sendMailableHtml')
            ->once()
            ->withArgs(fn ($to, $subject, $html, $text, $profile) => $profile === EmailProfile::Sales)
            ->andReturn(SendResult::fail('brevo', 'Unverified sender'));
        $emails->shouldReceive('sendMailableHtml')
            ->once()
            ->withArgs(fn ($to, $subject, $html, $text, $profile) => $profile === EmailProfile::NoReply)
            ->andReturn(SendResult::ok('brevo', 'msg-noreply'));

        $this->app->instance(EmailService::class, $emails);
        $this->app->forgetInstance(OutboundMail::class);

        $result = app(OutboundMail::class)->sendHtml(
            to: 'buyer@example.com',
            subject: 'Domain rejected',
            html: '<p>Hello</p>',
            profile: EmailProfile::Sales,
        );

        $this->assertTrue($result->success);
        $this->assertSame('msg-noreply', $result->messageId);
    }

    public function test_does_not_double_send_when_primary_is_noreply(): void
    {
        $emails = Mockery::mock(EmailService::class);
        $emails->shouldReceive('sendMailableHtml')
            ->once()
            ->withArgs(fn ($to, $subject, $html, $text, $profile) => $profile === EmailProfile::NoReply)
            ->andReturn(SendResult::fail('brevo', 'Down'));

        $this->app->instance(EmailService::class, $emails);
        $this->app->forgetInstance(OutboundMail::class);

        $result = app(OutboundMail::class)->sendHtml(
            to: 'buyer@example.com',
            subject: 'Test',
            html: '<p>Hi</p>',
            profile: EmailProfile::NoReply,
        );

        $this->assertFalse($result->success);
    }
}
