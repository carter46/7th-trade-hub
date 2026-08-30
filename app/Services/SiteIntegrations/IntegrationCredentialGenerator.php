<?php

namespace App\Services\SiteIntegrations;

use Illuminate\Support\Str;

class IntegrationCredentialGenerator
{
    /**
     * @return array{integration_id: string, client_id: string, client_secret: string, webhook_secret: string}
     */
    public function generate(): array
    {
        return [
            'integration_id' => (string) Str::uuid(),
            'client_id' => 'th_'.Str::lower(Str::random(24)),
            'client_secret' => Str::random(64),
            'webhook_secret' => Str::random(64),
        ];
    }
}
