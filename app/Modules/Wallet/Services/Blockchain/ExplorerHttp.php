<?php

namespace App\Modules\Wallet\Services\Blockchain;

use App\Models\IntegrationProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ExplorerHttp
{
    /**
     * GET with up to 3 immediate retries on timeout/5xx.
     *
     * @param  array<string, mixed>  $query
     * @param  array<string, string>  $headers
     * @return array{ok: bool, status: int, json: mixed, body: string, attempts: int, error: ?string}
     */
    public function get(string $url, array $query = [], array $headers = [], int $maxAttempts = 3): array
    {
        $maxAttempts = max(1, $maxAttempts);
        $lastError = null;
        $lastStatus = 0;
        $lastBody = '';
        $lastJson = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = Http::timeout(12)
                    ->acceptJson()
                    ->withHeaders(array_merge(['User-Agent' => '7th-trade-hub-otc'], $headers))
                    ->get($url, $query);

                $lastStatus = $response->status();
                $lastBody = $response->body();
                $lastJson = $response->json();

                if ($response->successful()) {
                    return [
                        'ok' => true,
                        'status' => $lastStatus,
                        'json' => $lastJson,
                        'body' => $lastBody,
                        'attempts' => $attempt,
                        'error' => null,
                    ];
                }

                if ($lastStatus < 500 && $lastStatus !== 429) {
                    break;
                }

                $lastError = 'HTTP '.$lastStatus;
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::channel('financial')->warning('Explorer HTTP attempt failed', [
                    'url' => $url,
                    'attempt' => $attempt,
                    'error' => $lastError,
                ]);
            }

            if ($attempt < $maxAttempts) {
                usleep(250_000 * $attempt);
            }
        }

        return [
            'ok' => false,
            'status' => $lastStatus,
            'json' => $lastJson,
            'body' => $lastBody,
            'attempts' => $maxAttempts,
            'error' => $lastError ?? 'Explorer request failed',
        ];
    }

    /**
     * POST JSON with the same retry policy as get().
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $headers
     * @return array{ok: bool, status: int, json: mixed, body: string, attempts: int, error: ?string}
     */
    public function postJson(string $url, array $payload = [], array $headers = [], int $maxAttempts = 3): array
    {
        $maxAttempts = max(1, $maxAttempts);
        $lastError = null;
        $lastStatus = 0;
        $lastBody = '';
        $lastJson = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = Http::timeout(12)
                    ->acceptJson()
                    ->asJson()
                    ->withHeaders(array_merge(['User-Agent' => '7th-trade-hub-otc'], $headers))
                    ->post($url, $payload);

                $lastStatus = $response->status();
                $lastBody = $response->body();
                $lastJson = $response->json();

                if ($response->successful()) {
                    return [
                        'ok' => true,
                        'status' => $lastStatus,
                        'json' => $lastJson,
                        'body' => $lastBody,
                        'attempts' => $attempt,
                        'error' => null,
                    ];
                }

                if ($lastStatus < 500 && $lastStatus !== 429) {
                    break;
                }

                $lastError = 'HTTP '.$lastStatus;
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::channel('financial')->warning('Explorer HTTP POST attempt failed', [
                    'url' => $url,
                    'attempt' => $attempt,
                    'error' => $lastError,
                ]);
            }

            if ($attempt < $maxAttempts) {
                usleep(250_000 * $attempt);
            }
        }

        return [
            'ok' => false,
            'status' => $lastStatus,
            'json' => $lastJson,
            'body' => $lastBody,
            'attempts' => $maxAttempts,
            'error' => $lastError ?? 'Explorer request failed',
        ];
    }

    public function monitoringProvider(): IntegrationProvider
    {
        return IntegrationProvider::forProvider(IntegrationProvider::BLOCKCHAIN_MONITORING);
    }

    public function maxRetries(): int
    {
        return (int) config('crypto.monitor_max_retries', 3);
    }
}
