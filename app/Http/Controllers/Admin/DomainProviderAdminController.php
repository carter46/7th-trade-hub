<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainProvider;
use App\Services\Domains\DomainAuditLogger;
use App\Services\Domains\DomainCacheInvalidator;
use App\Services\Domains\DomainProviderConfigValidator;
use App\Services\Domains\DomainProviderManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DomainProviderAdminController extends Controller
{
    public function __construct(
        private DomainProviderManager $manager,
        private DomainCacheInvalidator $cacheInvalidator,
        private DomainProviderConfigValidator $configValidator,
        private DomainAuditLogger $audit,
    ) {}

    public function index(): View
    {
        $providers = DomainProvider::query()->orderByDesc('is_default')->orderBy('fallback_priority')->get();

        return view('dashboard.admin.domain-providers.index', compact('providers'));
    }

    public function edit(DomainProvider $domainProvider): View
    {
        $sandboxHint = config('domains.providers.'.$domainProvider->key.'.sandbox_hint');

        return view('dashboard.admin.domain-providers.edit', [
            'provider' => $domainProvider,
            'credentialLabels' => $domainProvider->credentialLabels(),
            'sandboxHint' => $sandboxHint,
        ]);
    }

    public function update(Request $request, DomainProvider $domainProvider): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['sometimes', 'boolean'],
            'is_default' => ['sometimes', 'boolean'],
            'fallback_priority' => ['nullable', 'integer', 'min:1', 'max:999'],
            'sandbox' => ['sometimes', 'boolean'],
            'credentials' => ['nullable', 'array'],
            'credentials.*' => ['nullable', 'string', 'max:500'],
        ]);

        $enabled = $request->boolean('enabled');
        $isDefault = $request->boolean('is_default');
        $fallbackPriority = filled($data['fallback_priority'] ?? null) ? (int) $data['fallback_priority'] : null;
        $credentials = $domainProvider->credentials ?? [];
        $labels = $domainProvider->credentialLabels();
        $oldSnapshot = $domainProvider->only(['enabled', 'is_default', 'fallback_priority', 'sandbox']);

        foreach ($labels as $key => $label) {
            $value = $data['credentials'][$key] ?? null;
            if (filled($value)) {
                $credentials[$key] = $value;
            }
        }

        if ($enabled) {
            foreach (array_keys($labels) as $key) {
                if (! filled($credentials[$key] ?? null)) {
                    return back()->withInput()->with('error', 'Enter all required credentials before enabling this provider.');
                }
            }
        }

        try {
            $this->configValidator->validateSave($domainProvider, $enabled, $isDefault, $fallbackPriority);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        if ($isDefault) {
            DomainProvider::query()->where('id', '!=', $domainProvider->id)->update(['is_default' => false]);
        }

        $domainProvider->update([
            'enabled' => $enabled,
            'is_default' => $isDefault,
            'fallback_priority' => $isDefault ? null : $fallbackPriority,
            'sandbox' => $request->boolean('sandbox'),
            'credentials' => $credentials,
        ]);

        if ($enabled && ! DomainProvider::query()->where('enabled', true)->where('is_default', true)->exists()) {
            return back()->with('error', 'Enable a default provider or mark this one as default.');
        }

        $this->cacheInvalidator->invalidateAllDomainPricingCaches();
        $this->audit->providerConfigChanged(
            $domainProvider->fresh(),
            $oldSnapshot,
            $domainProvider->only(['enabled', 'is_default', 'fallback_priority', 'sandbox']),
            $request->user()?->id,
        );

        return redirect()->route('admin.domain-providers')->with('status', 'Provider updated.');
    }

    public function test(DomainProvider $domainProvider): RedirectResponse
    {
        try {
            $adapter = $this->manager->adapterFor($domainProvider);
            $ok = $adapter->testConnection($domainProvider);
            $domainProvider->update([
                'health_status' => $ok ? DomainProvider::HEALTH_HEALTHY : DomainProvider::HEALTH_DEGRADED,
                'last_health_check_at' => now(),
            ]);

            return back()->with('status', 'Connection successful.');
        } catch (\Throwable $e) {
            $domainProvider->update([
                'health_status' => DomainProvider::HEALTH_UNAVAILABLE,
                'last_health_check_at' => now(),
            ]);

            return back()->with('error', 'Connection failed. Check credentials and sandbox settings.');
        }
    }
}
