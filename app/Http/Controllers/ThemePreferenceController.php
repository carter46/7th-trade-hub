<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateThemePreferenceRequest;
use App\Services\ThemeManager;
use Illuminate\Http\JsonResponse;

class ThemePreferenceController extends Controller
{
    public function __construct(private ThemeManager $themes) {}

    public function update(UpdateThemePreferenceRequest $request): JsonResponse
    {
        $preference = $request->validated('theme');
        $systemTheme = $request->validated('system_theme');
        $user = $request->user();
        $user->forceFill(['theme_preference' => $preference])->save();

        // system_theme is a client hint for charts/assets only; paint still resolves via matchMedia on the client.
        $payload = $this->themes->payloadFor($user->fresh(), $systemTheme);

        return response()->json([
            'ok' => true,
            'preference' => $payload['preference'],
            // For system, resolved is advisory (may be light if no hint). Client must re-resolve via matchMedia.
            'resolved' => $payload['resolved'],
            'charts' => $payload['charts'],
            'assets' => $payload['assets'],
            'themes' => $payload['themes'],
        ]);
    }
}
