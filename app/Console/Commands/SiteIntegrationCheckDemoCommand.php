<?php

namespace App\Console\Commands;

use App\Models\SiteIntegration;
use App\Services\SiteIntegrations\ConnectionCheckService;
use Illuminate\Console\Command;

class SiteIntegrationCheckDemoCommand extends Command
{
    protected $signature = 'site-integrations:check-demo {integration : Site integration id or UUID}';

    protected $description = 'Run Hub → merchant health check for a demo site integration and print diagnostics';

    public function handle(ConnectionCheckService $checks): int
    {
        $key = (string) $this->argument('integration');

        $integration = SiteIntegration::query()
            ->when(
                ctype_digit($key),
                fn ($q) => $q->whereKey((int) $key),
                fn ($q) => $q->where('integration_id', $key),
            )
            ->first();

        if (! $integration) {
            $this->error('Demo site integration not found: '.$key);

            return self::FAILURE;
        }

        $this->info('Checking: '.$integration->name);
        $this->line('Base URL: '.$integration->base_url);
        $this->line('Health URL: '.$integration->healthUrl());

        $result = $checks->checkDemo($integration);

        $this->newLine();
        $this->line($result['ok'] ? '<info>OK</info>' : '<error>FAIL</error>');
        $this->line('HTTP status: '.($result['http_status'] ?? 'n/a'));
        $this->line('Message: '.$result['message']);

        if (is_array($result['payload'] ?? null)) {
            $this->newLine();
            $this->line('Payload summary (no secrets):');
            $this->line(json_encode($result['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}');
        }

        return $result['ok'] ? self::SUCCESS : self::FAILURE;
    }
}
