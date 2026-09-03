<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\SiteIntegration;
use App\Models\SiteIntegrationCheckLog;
use App\Models\UserToolIntegration;
use App\Services\SiteIntegrations\OwnedAdminCredentialSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class SiteIntegrationWebhookController extends Controller
{
    public function __construct(
        private OwnedAdminCredentialSyncService $credentialSync,
    ) {}

    public function __invoke(Request $request, string $integrationId): JsonResponse
    {
        $secret = (string) $request->header('X-7TH-Webhook-Secret', '');

        $demo = SiteIntegration::query()->where('integration_id', $integrationId)->first();
        $owned = $demo ? null : UserToolIntegration::query()->where('integration_id', $integrationId)->first();

        if (! $demo && ! $owned) {
            return response()->json(['message' => 'Unknown integration.'], 404);
        }

        $expected = $demo?->webhook_secret ?? $owned?->webhook_secret;
        if ($secret === '' || ! is_string($expected) || ! hash_equals($expected, $secret)) {
            return response()->json(['message' => 'Invalid webhook secret.'], 401);
        }

        $payload = $request->json()->all();
        if ($payload === []) {
            $payload = $request->all();
        }

        $event = (string) ($payload['event'] ?? $request->input('event') ?? 'ping');

        if ($event === OwnedAdminCredentialSyncService::EVENT) {
            return $this->handleCredentialSync($request, $demo, $owned, $payload);
        }

        SiteIntegrationCheckLog::create([
            'owner_type' => $demo ? 'demo' : 'owned',
            'owner_id' => $demo?->id ?? $owned->id,
            'direction' => 'site_to_hub',
            'ok' => true,
            'http_status' => 200,
            'message' => 'Webhook received: '.$event,
            'payload_summary' => [
                'event' => $event,
            ],
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function handleCredentialSync(
        Request $request,
        ?SiteIntegration $demo,
        ?UserToolIntegration $owned,
        array $payload,
    ): JsonResponse {
        if ($demo || ! $owned) {
            return response()->json(['message' => 'Admin credential sync is only supported for owned integrations.'], 403);
        }

        $clientId = (string) $request->header('X-7TH-Client-Id', '');
        if ($clientId === '' || ! hash_equals((string) $owned->client_id, $clientId)) {
            return response()->json(['message' => 'Invalid client credentials.'], 401);
        }

        try {
            $result = $this->credentialSync->apply($owned->load('userTool'), $payload, $request->ip());
        } catch (InvalidArgumentException $e) {
            $status = str_contains(strtolower($e->getMessage()), 'signature') ? 401 : 422;

            return response()->json(['message' => $e->getMessage()], $status);
        }

        return response()->json($result);
    }
}
