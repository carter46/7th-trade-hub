<?php

namespace App\Services\SiteIntegrations;

use App\Models\SiteIntegration;
use App\Models\SiteIntegrationCheckLog;
use App\Models\UserTool;
use App\Models\UserToolIntegration;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class ConnectionCheckService
{
    public function __construct(
        private ProtocolV1Signer $signer,
        private IntegrationHttpClient $http,
    ) {}

    /**
     * @return array{ok: bool, http_status: int|null, message: string, payload: array<string, mixed>|null}
     */
    public function checkDemo(SiteIntegration $integration): array
    {
        $result = $this->ping(
            $integration->healthUrl(),
            $integration->integration_id,
            $integration->client_id,
            $integration->client_secret,
            'demo',
        );

        $integration->connection_status = $result['ok'] ? 'ok' : 'error';
        $integration->last_checked_at = now();
        $integration->last_error = $result['ok'] ? null : $result['message'];
        $integration->save();

        SiteIntegrationCheckLog::create([
            'owner_type' => 'demo',
            'owner_id' => $integration->id,
            'direction' => 'hub_to_site',
            'ok' => $result['ok'],
            'http_status' => $result['http_status'],
            'message' => $result['message'],
            'payload_summary' => $result['payload'],
        ]);

        return $result;
    }

    /**
     * @return array{ok: bool, http_status: int|null, message: string, payload: array<string, mixed>|null}
     */
    public function checkOwned(UserTool $tool): array
    {
        $integration = $tool->integration;
        if (! $integration instanceof UserToolIntegration || ! $tool->site_url) {
            $result = [
                'ok' => false,
                'http_status' => null,
                'message' => 'Missing site URL or provisioning integration.',
                'payload' => null,
            ];
        } else {
            $result = $this->ping(
                $tool->healthUrl(),
                $integration->integration_id,
                $integration->client_id,
                $integration->client_secret,
                'owned_tool',
            );

            $integration->connection_status = $result['ok'] ? 'ok' : 'error';
            $integration->last_checked_at = now();
            $integration->last_error = $result['ok'] ? null : $result['message'];
            $integration->save();

            SiteIntegrationCheckLog::create([
                'owner_type' => 'owned',
                'owner_id' => $integration->id,
                'direction' => 'hub_to_site',
                'ok' => $result['ok'],
                'http_status' => $result['http_status'],
                'message' => $result['message'],
                'payload_summary' => $result['payload'],
            ]);
        }

        return $result;
    }

    /**
     * @return array{ok: bool, http_status: int|null, message: string, payload: array<string, mixed>|null}
     */
    private function ping(string $url, string $integrationId, string $clientId, string $clientSecret, string $context): array
    {
        $payload = $this->signer->sign([
            'integration_id' => $integrationId,
            'context' => $context,
            'role' => 'health',
            'identity' => ['email' => 'health@7th-tradehub.local'],
            'request_id' => (string) Str::uuid(),
            'nonce' => Str::random(24),
            'issued_at' => now()->toIso8601String(),
            'expires_at' => now()->addMinutes(2)->toIso8601String(),
        ], $clientSecret);

        try {
            $response = $this->http->postJson(
                $url,
                [
                    'X-7TH-Client-Id' => $clientId,
                    'X-7TH-Integration-Id' => $integrationId,
                ],
                $payload,
                15
            );

            $body = $response->json();
            $ok = $response->successful() && (($body['ok'] ?? false) === true);

            return [
                'ok' => $ok,
                'http_status' => $response->status(),
                'message' => $ok
                    ? 'Connection successful.'
                    : ('Health check failed: '.($body['message'] ?? $response->body() ?: 'HTTP '.$response->status())),
                'payload' => is_array($body) ? [
                    'ok' => $body['ok'] ?? null,
                    'capabilities' => $body['capabilities'] ?? null,
                ] : null,
            ];
        } catch (InvalidArgumentException $e) {
            return [
                'ok' => false,
                'http_status' => null,
                'message' => 'Connection blocked: '.$e->getMessage(),
                'payload' => null,
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'http_status' => null,
                'message' => 'Connection error: '.$e->getMessage(),
                'payload' => null,
            ];
        }
    }
}
