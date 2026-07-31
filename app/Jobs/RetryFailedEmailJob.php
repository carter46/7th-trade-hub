<?php

namespace App\Jobs;

use App\Services\Communications\Email\EmailService;
use App\Services\Communications\Email\OutgoingEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RetryFailedEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        public OutgoingEmail $email,
        public int $attemptStage = 1,
    ) {}

    public function handle(EmailService $emails): void
    {
        $emails->send($this->email, deferredStage: $this->attemptStage);
    }
}
