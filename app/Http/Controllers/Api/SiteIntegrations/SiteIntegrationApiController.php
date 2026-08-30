<?php

namespace App\Http\Controllers\Api\SiteIntegrations;

use App\Http\Controllers\Controller;
use App\Models\SiteIntegration;
use App\Models\UserToolIntegration;
use App\Services\SiteIntegrations\DemoLaunchService;
use App\Services\SiteIntegrations\SubscriptionSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class SiteIntegrationApiController extends Controller
{
    public function __construct(
        private DemoLaunchService $launch,
        private SubscriptionSyncService $subscriptionSync,
    ) {}

    /**
     * Validate and consume a one-time launch token.
     * Auth: X-7TH-Client-Id + X-7TH-Client-Secret headers (per-integration).
     */
    public function validateToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $clientId = (string) $request->header('X-7TH-Client-Id', '');
        $clientSecret = (string) $request->header('X-7TH-Client-Secret', '');

        if ($clientId === '' || $clientSecret === '') {
            return response()->json(['message' => 'Missing client credentials.'], 401);
        }

        if (! $this->credentialsMatch($clientId, $clientSecret)) {
            return response()->json(['message' => 'Invalid client credentials.'], 401);
        }

        try {
            $payload = $this->launch->validateAndConsume($data['token'], $clientId);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return response()->json(['message' => $e->getMessage(), 'valid' => false], 422);
        }

        unset($payload['client_secret_hint']);

        return response()->json($payload);
    }

    /**
     * Site polls Hub for current subscription state (defense in depth).
     */
    public function subscriptionStatus(Request $request): JsonResponse
    {
        $clientId = (string) $request->header('X-7TH-Client-Id', '');
        $clientSecret = (string) $request->header('X-7TH-Client-Secret', '');
        $integrationId = (string) $request->header('X-7TH-Integration-Id', $request->query('integration_id', ''));

        if ($clientId === '' || $clientSecret === '' || $integrationId === '') {
            return response()->json(['message' => 'Missing client credentials.'], 401);
        }

        $integration = UserToolIntegration::query()
            ->where('integration_id', $integrationId)
            ->where('client_id', $clientId)
            ->first();

        if (! $integration || ! hash_equals($integration->client_secret, $clientSecret)) {
            return response()->json(['message' => 'Invalid client credentials.'], 401);
        }

        $snapshot = $this->subscriptionSync->snapshotForClient($integrationId, $clientId);
        if (! $snapshot) {
            return response()->json(['message' => 'Tool not found.'], 404);
        }

        return response()->json($snapshot);
    }

    private function credentialsMatch(string $clientId, string $clientSecret): bool
    {
        $demo = SiteIntegration::query()->where('client_id', $clientId)->first();
        if ($demo && hash_equals($demo->client_secret, $clientSecret)) {
            return true;
        }

        $owned = UserToolIntegration::query()->where('client_id', $clientId)->first();

        return $owned && hash_equals($owned->client_secret, $clientSecret);
    }
}
