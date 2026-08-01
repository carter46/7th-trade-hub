<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\IntegrationProvider;
use App\Models\SystemSetting;
use App\Models\TrackingScript;
use App\Modules\Admin\Services\AuditLogService;
use App\Services\Analytics\Providers\GoogleAnalyticsProvider;
use App\Services\Analytics\Providers\MicrosoftClarityProvider;
use App\Services\Tracking\Providers\GoogleTagManagerProvider;
use App\Services\Tracking\Providers\MetaPixelProvider;
use App\Services\Tracking\TrackingDuplicateDetector;
use App\Services\Tracking\TrackingScriptRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TrackingSettingsController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private TrackingScriptRenderer $renderer,
        private TrackingDuplicateDetector $duplicates,
        private GoogleTagManagerProvider $gtm,
        private GoogleAnalyticsProvider $ga,
        private MetaPixelProvider $meta,
        private MicrosoftClarityProvider $clarity,
    ) {}

    public function index(): View
    {
        $scripts = Schema::hasTable('tracking_scripts')
            ? TrackingScript::query()->orderBy('sort_order')->orderBy('id')->get()
            : collect();

        return view('dashboard.admin.tracking', [
            'gtm' => IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_TAG_MANAGER),
            'ga' => IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_ANALYTICS),
            'clarity' => IntegrationProvider::forProvider(IntegrationProvider::MICROSOFT_CLARITY),
            'meta' => IntegrationProvider::forProvider(IntegrationProvider::META_PIXEL),
            'verificationGoogle' => (string) SystemSetting::get('verification_google', ''),
            'verificationBing' => (string) SystemSetting::get('verification_bing', ''),
            'verificationFacebook' => (string) SystemSetting::get('verification_facebook', ''),
            'scripts' => $scripts,
            'duplicateConflicts' => $this->duplicates->conflicts(),
            'duplicateLabels' => $this->duplicates->labelsByScriptId($scripts),
            'preview' => $this->renderer->itemsForPreview(),
        ]);
    }

    public function updateProviders(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gtm_enabled' => ['nullable', 'boolean'],
            'gtm_container_id' => ['nullable', 'string', 'max:32'],
            'google_enabled' => ['nullable', 'boolean'],
            'google_measurement_id' => ['nullable', 'string', 'max:32'],
            'google_property_id' => ['nullable', 'string', 'max:32'],
            'clarity_enabled' => ['nullable', 'boolean'],
            'clarity_project_id' => ['nullable', 'string', 'max:64'],
            'meta_enabled' => ['nullable', 'boolean'],
            'meta_pixel_id' => ['nullable', 'string', 'max:20'],
            'verification_google' => ['nullable', 'string', 'max:255'],
            'verification_bing' => ['nullable', 'string', 'max:255'],
            'verification_facebook' => ['nullable', 'string', 'max:255'],
        ]);

        $gtmEnabled = $request->boolean('gtm_enabled');
        $googleEnabled = $request->boolean('google_enabled');
        $clarityEnabled = $request->boolean('clarity_enabled');
        $metaEnabled = $request->boolean('meta_enabled');

        $gtmId = trim((string) ($validated['gtm_container_id'] ?? ''));
        $gaId = trim((string) ($validated['google_measurement_id'] ?? ''));
        $clarityId = trim((string) ($validated['clarity_project_id'] ?? ''));
        $metaId = trim((string) ($validated['meta_pixel_id'] ?? ''));

        $errors = [];
        if ($gtmEnabled && $gtmId === '') {
            $errors['gtm_container_id'] = 'Container ID is required when Google Tag Manager is enabled.';
        } elseif ($gtmId !== '' && ! preg_match('/^GTM-[A-Z0-9]+$/i', $gtmId)) {
            $errors['gtm_container_id'] = 'Container ID must match GTM-XXXXXXX (do not paste the full script).';
        }
        if ($googleEnabled && $gaId === '') {
            $errors['google_measurement_id'] = 'Measurement ID is required when Google Analytics is enabled.';
        } elseif ($gaId !== '' && ! preg_match('/^G-[A-Z0-9]+$/i', $gaId)) {
            $errors['google_measurement_id'] = 'Measurement ID must match G-XXXXXXXXXX (do not paste the full script).';
        }
        if ($clarityEnabled && $clarityId === '') {
            $errors['clarity_project_id'] = 'Project ID is required when Microsoft Clarity is enabled.';
        } elseif ($clarityId !== '' && ! preg_match('/^[A-Za-z0-9]+$/', $clarityId)) {
            $errors['clarity_project_id'] = 'Project ID must be alphanumeric (do not paste the full script).';
        }
        if ($metaEnabled && $metaId === '') {
            $errors['meta_pixel_id'] = 'Pixel ID is required when Meta Pixel is enabled.';
        } elseif ($metaId !== '' && ! preg_match('/^\d{5,20}$/', $metaId)) {
            $errors['meta_pixel_id'] = 'Pixel ID must be 5–20 digits (do not paste the full script).';
        }
        if ($errors !== []) {
            return back()->withInput()->withErrors($errors);
        }

        $gtm = IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_TAG_MANAGER);
        $gtm->fill(['enabled' => $gtmEnabled, 'status' => $gtmEnabled ? 'configured' : 'idle']);
        $gtm->mergeCredentials([
            'container_id' => strtoupper(trim((string) ($validated['gtm_container_id'] ?? ''))),
        ]);
        $gtm->save();

        $ga = IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_ANALYTICS);
        $ga->fill(['enabled' => $googleEnabled, 'status' => $googleEnabled ? 'configured' : 'idle']);
        $ga->mergeCredentials([
            'measurement_id' => strtoupper(trim((string) ($validated['google_measurement_id'] ?? ''))),
            'property_id' => trim((string) ($validated['google_property_id'] ?? '')),
        ]);
        $ga->save();

        $clarity = IntegrationProvider::forProvider(IntegrationProvider::MICROSOFT_CLARITY);
        $clarity->fill(['enabled' => $clarityEnabled, 'status' => $clarityEnabled ? 'configured' : 'idle']);
        $clarity->mergeCredentials([
            'project_id' => trim((string) ($validated['clarity_project_id'] ?? '')),
        ]);
        $clarity->save();

        $meta = IntegrationProvider::forProvider(IntegrationProvider::META_PIXEL);
        $meta->fill(['enabled' => $metaEnabled, 'status' => $metaEnabled ? 'configured' : 'idle']);
        $meta->mergeCredentials([
            'pixel_id' => trim((string) ($validated['meta_pixel_id'] ?? '')),
        ]);
        $meta->save();

        SystemSetting::set('verification_google', trim((string) ($validated['verification_google'] ?? '')));
        SystemSetting::set('verification_bing', trim((string) ($validated['verification_bing'] ?? '')));
        SystemSetting::set('verification_facebook', trim((string) ($validated['verification_facebook'] ?? '')));

        $this->renderer->flushCache();

        $this->audit->log(auth()->id(), 'settings.tracking.updated', null, null, [
            'gtm_enabled' => $gtmEnabled,
            'google_enabled' => $googleEnabled,
            'clarity_enabled' => $clarityEnabled,
            'meta_enabled' => $metaEnabled,
        ], $request->ip());

        $warnings = $this->duplicates->conflicts();
        $redirect = redirect()
            ->route('admin.tracking')
            ->with('status', __('Marketing & tracking settings saved.'));

        if ($warnings !== []) {
            $redirect->with('warning', $warnings[0]['message']);
        }

        return $redirect;
    }

    public function testProvider(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'in:google_tag_manager,google_analytics,microsoft_clarity,meta_pixel'],
            'gtm_container_id' => ['nullable', 'string', 'max:32'],
            'google_measurement_id' => ['nullable', 'string', 'max:32'],
            'clarity_project_id' => ['nullable', 'string', 'max:64'],
            'meta_pixel_id' => ['nullable', 'string', 'max:20'],
        ]);

        $result = match ($validated['provider']) {
            IntegrationProvider::GOOGLE_TAG_MANAGER => $this->gtm->connectionTestFromInput([
                'container_id' => $this->resolveId(
                    $validated['gtm_container_id'] ?? null,
                    IntegrationProvider::GOOGLE_TAG_MANAGER,
                    'container_id'
                ),
            ]),
            IntegrationProvider::GOOGLE_ANALYTICS => $this->ga->connectionTestFromInput([
                'measurement_id' => $this->resolveId(
                    $validated['google_measurement_id'] ?? null,
                    IntegrationProvider::GOOGLE_ANALYTICS,
                    'measurement_id'
                ),
            ]),
            IntegrationProvider::MICROSOFT_CLARITY => $this->clarity->connectionTestFromInput([
                'project_id' => $this->resolveId(
                    $validated['clarity_project_id'] ?? null,
                    IntegrationProvider::MICROSOFT_CLARITY,
                    'project_id'
                ),
            ]),
            default => $this->meta->connectionTestFromInput([
                'pixel_id' => $this->resolveId(
                    $validated['meta_pixel_id'] ?? null,
                    IntegrationProvider::META_PIXEL,
                    'pixel_id'
                ),
            ]),
        };

        $provider = IntegrationProvider::forProvider($validated['provider']);
        if ($result['ok']) {
            $provider->recordSuccess();
        } else {
            $provider->recordFailure($result['message']);
        }

        $this->audit->log(auth()->id(), 'settings.tracking.connection_test', null, null, [
            'provider' => $validated['provider'],
            'ok' => $result['ok'],
        ], $request->ip());

        if (! $result['ok']) {
            return back()->withInput()->withErrors([
                'tracking_connection' => $result['message'],
            ]);
        }

        return back()->with('status', $result['message']);
    }

    public function storeScript(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('tracking_scripts')) {
            return back()->with('error', __('Run migrations before managing custom scripts (tracking_scripts table missing).'));
        }

        $validated = $this->validateScript($request);

        if (preg_match('/<\?(php|=)?/i', $validated['code']) || str_contains($validated['code'], '<%')) {
            return back()->withInput()->withErrors([
                'code' => 'Server-side code is not allowed in custom scripts.',
            ]);
        }

        $maxOrder = (int) TrackingScript::query()->max('sort_order');

        $script = TrackingScript::query()->create([
            'name' => $validated['name'],
            'location' => $validated['location'],
            'enabled' => $request->boolean('enabled', true),
            'code' => $validated['code'],
            'sort_order' => $maxOrder + 1,
        ]);

        $this->renderer->flushCache();

        $this->audit->log(auth()->id(), 'settings.tracking.script_created', null, null, [
            'script_id' => $script->id,
            'name' => $script->name,
            'location' => $script->location,
            'enabled' => $script->enabled,
        ], $request->ip());

        $redirect = redirect()
            ->route('admin.tracking')
            ->with('status', __('Custom script saved.'));

        $conflicts = $this->duplicates->conflicts($script);
        if ($conflicts !== []) {
            $redirect->with('warning', $conflicts[0]['message']);
        }

        return $redirect;
    }

    public function updateScript(Request $request, TrackingScript $script): RedirectResponse
    {
        $validated = $this->validateScript($request);

        if (preg_match('/<\?(php|=)?/i', $validated['code']) || str_contains($validated['code'], '<%')) {
            return back()->withInput()->withErrors([
                'code' => 'Server-side code is not allowed in custom scripts.',
            ]);
        }

        $script->update([
            'name' => $validated['name'],
            'location' => $validated['location'],
            'enabled' => $request->boolean('enabled'),
            'code' => $validated['code'],
        ]);

        $this->renderer->flushCache();

        $this->audit->log(auth()->id(), 'settings.tracking.script_updated', null, null, [
            'script_id' => $script->id,
            'name' => $script->name,
            'location' => $script->location,
            'enabled' => $script->enabled,
        ], $request->ip());

        $redirect = redirect()
            ->route('admin.tracking')
            ->with('status', __('Custom script updated.'));

        $conflicts = $this->duplicates->conflicts($script->fresh());
        if ($conflicts !== []) {
            $redirect->with('warning', $conflicts[0]['message']);
        }

        return $redirect;
    }

    public function destroyScript(TrackingScript $script): RedirectResponse
    {
        $id = $script->id;
        $name = $script->name;
        $script->delete();
        $this->renderer->flushCache();

        $this->audit->log(auth()->id(), 'settings.tracking.script_deleted', null, null, [
            'script_id' => $id,
            'name' => $name,
        ], request()->ip());

        return redirect()
            ->route('admin.tracking')
            ->with('status', __('Custom script deleted.'));
    }

    /**
     * @return array{name: string, location: string, code: string}
     */
    private function validateScript(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'location' => ['required', Rule::in(TrackingScript::LOCATIONS)],
            'enabled' => ['nullable', 'boolean'],
            'code' => ['required', 'string', 'max:50000'],
        ]);
    }

    private function resolveId(?string $input, string $provider, string $credentialKey): string
    {
        $value = trim((string) $input);
        if ($value !== '') {
            return $value;
        }

        return (string) (IntegrationProvider::forProvider($provider)->credential($credentialKey) ?? '');
    }
}
