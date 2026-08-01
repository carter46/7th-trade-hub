<?php

namespace App\Jobs;

use App\Models\PaymentWebhook;
use App\Modules\Wallet\Payments\Monnify\MonnifyWebhookProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMonnifyWebhook implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $webhookId) {}

    public function handle(MonnifyWebhookProcessor $processor): void
    {
        $webhook = PaymentWebhook::find($this->webhookId);
        if (! $webhook) {
            return;
        }

        $processor->process($webhook);
    }
}
