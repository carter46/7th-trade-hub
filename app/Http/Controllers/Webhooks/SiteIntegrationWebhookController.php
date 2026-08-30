<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\SiteIntegration;
use App\Models\SiteIntegrationCheckLog;
use App\Models\UserToolIntegration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteIntegrationWebhookController extends Controller
{
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

        SiteIntegrationCheckLog::create([
            'owner_type' => $demo ? 'demo' : 'owned',
            'owner_id' => $demo?->id ?? $owned->id,
            'direction' => 'site_to_hub',
            'ok' => true,
            'http_status' => 200,
            'message' => 'Webhook received: '.($request->input('event') ?? 'ping'),
            'payload_summary' => [
                'event' => $request->input('event'),
            ],
        ]);

        return response()->json(['ok' => true]);
    }
}
