<?php

namespace App\Services\SiteIntegrations;

use App\Models\UserTool;
use App\Models\UserToolIntegration;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class SubscriptionSyncService
{
    public function __construct(
        private ProtocolV1Signer $signer,
        private IntegrationHttpClient $http,
    ) {}

    /**
     * @return array{ok: bool, message: string, http_status: int|null}
     */
    public function push(UserTool $tool): array
    {
        $integration = $tool->integration;
        if (! $integration instanceof UserToolIntegration || ! $tool->site_url) {
            return [
                'ok' => false,
                'message' => 'Missing provisioning integration or site URL.',
                'http_status' => null,
            ];
        }

        if (! $integration->hasCapability(UserToolIntegration::CAP_SUBSCRIPTION_SYNC)) {
            return [
                'ok' => false,
                'message' => 'subscription_sync capability not enabled.',
                'http_status' => null,
            ];
        }

        $effective = $tool->effectiveStatus();
        $status = $effective->value;

        $body = $this->signer->sign([
            'integration_id' => $integration->integration_id,
            'context' => 'owned_tool',
            'role' => 'subscription',
            'identity' => ['email' => $tool->admin_email ?? ''],
            'request_id' => (string) Str::uuid(),
            'nonce' => Str::random(24),
            'issued_at' => now()->toIso8601String(),
            'expires_at' => now()->addMinutes(5)->toIso8601String(),
            'subscription' => [
                'tool_id' => $tool->id,
                'public_id' => $tool->public_id,
                'status' => $status,
                'expires_at' => $tool->expires_at?->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ], $integration->client_secret);

        try {
            $response = $this->http->postJson(
                $tool->subscriptionSyncUrl(),
                [
                    'X-7TH-Client-Id' => $integration->client_id,
                    'X-7TH-Integration-Id' => $integration->integration_id,
                ],
                $body,
                20
            );

            $ok = $response->successful();
            $tool->last_synced_at = now();
            $tool->save();

            if (! $ok) {
                $integration->last_error = 'Subscription sync failed: HTTP '.$response->status();
                $integration->connection_status = 'error';
                $integration->save();
            }

            return [
                'ok' => $ok,
                'message' => $ok ? 'Subscription synced.' : ('Sync failed: HTTP '.$response->status()),
                'http_status' => $response->status(),
            ];
        } catch (InvalidArgumentException $e) {
            $integration->last_error = 'Subscription sync blocked: '.$e->getMessage();
            $integration->connection_status = 'error';
            $integration->save();

            return [
                'ok' => false,
                'message' => $e->getMessage(),
                'http_status' => null,
            ];
        } catch (Throwable $e) {
            $integration->last_error = 'Subscription sync error: '.$e->getMessage();
            $integration->connection_status = 'error';
            $integration->save();

            return [
                'ok' => false,
                'message' => $e->getMessage(),
                'http_status' => null,
            ];
        }
    }

    /**
     * Public subscription snapshot for site polling (authenticated by client credentials).
     *
     * @return array<string, mixed>|null
     */
    public function snapshotForClient(string $integrationId, string $clientId): ?array
    {
        $integration = UserToolIntegration::query()
            ->where('integration_id', $integrationId)
            ->where('client_id', $clientId)
            ->with('userTool')
            ->first();

        if (! $integration?->userTool) {
            return null;
        }

        $tool = $integration->userTool;
        $effective = $tool->effectiveStatus();

        return [
            'protocol' => ProtocolV1Signer::PROTOCOL,
            'version' => ProtocolV1Signer::VERSION,
            'tool_id' => $tool->id,
            'public_id' => $tool->public_id,
            'status' => $effective->value,
            'expires_at' => $tool->expires_at?->toIso8601String(),
            'updated_at' => $tool->updated_at?->toIso8601String(),
        ];
    }
}
