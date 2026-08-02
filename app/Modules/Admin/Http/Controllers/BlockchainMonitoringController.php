<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\IntegrationProvider;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Wallet\Services\Blockchain\ExplorerClientRegistry;
use App\Modules\Wallet\Services\Blockchain\MonitoredNetworkCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class BlockchainMonitoringController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
    ) {}

    public function index(): View
    {
        return view('dashboard.admin.blockchain-monitoring', [
            'blockchain' => IntegrationProvider::forProvider(IntegrationProvider::BLOCKCHAIN_MONITORING),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $enabledProviders = collect(config('crypto.monitor_providers', []))
            ->filter(fn ($p) => ($p['enabled'] ?? false) === true)
            ->keys()
            ->all();

        $credentialKeys = config('crypto.monitor_credential_keys', [
            'etherscan_api_key',
            'trongrid_api_key',
            'solana_rpc_url',
            'blockchain_com_api_key',
            'blockchain_com_base_url',
        ]);

        $rules = [
            'blockchain_enabled' => ['nullable', 'boolean'],
            'monitor_provider' => ['required', 'string', 'in:'.implode(',', $enabledProviders ?: ['native'])],
            'poll_interval_minutes' => ['nullable', 'integer', 'in:1,2'],
        ];
        foreach ($credentialKeys as $key) {
            $rules[$key] = ['nullable', 'string', 'max:500'];
        }

        $validated = $request->validate($rules);

        $row = IntegrationProvider::forProvider(IntegrationProvider::BLOCKCHAIN_MONITORING);
        $row->enabled = $request->boolean('blockchain_enabled');

        $credentials = [];
        foreach ($credentialKeys as $key) {
            $value = trim((string) ($validated[$key] ?? ''));
            if ($value !== '') {
                $credentials[$key] = $value;
            }
        }
        if ($credentials !== []) {
            $row->mergeCredentials($credentials);
        }

        $row->meta = array_merge($row->meta ?? [], [
            'monitor_provider' => (string) $validated['monitor_provider'],
            'poll_interval_minutes' => (int) ($validated['poll_interval_minutes'] ?? ($row->meta['poll_interval_minutes'] ?? 1)),
        ]);
        $row->status = $row->enabled ? ($row->status ?: 'idle') : 'idle';
        $row->save();

        $this->audit->log(auth()->id(), 'settings.blockchain.updated', $row, null, [
            'enabled' => $row->enabled,
            'monitor_provider' => $row->meta['monitor_provider'] ?? 'native',
        ], $request->ip());

        return back()->with('status', __('Blockchain monitoring settings saved.'));
    }

    public function test(Request $request): RedirectResponse|JsonResponse
    {
        $networkIds = array_keys(config('crypto.monitored_networks', []));
        $network = $request->validate([
            'network' => ['required', 'string', 'in:'.implode(',', $networkIds ?: ['bitcoin'])],
        ])['network'];

        $row = IntegrationProvider::forProvider(IntegrationProvider::BLOCKCHAIN_MONITORING);
        $catalog = app(MonitoredNetworkCatalog::class);
        $registry = app(ExplorerClientRegistry::class);
        $wantsJson = $request->expectsJson() || $request->ajax();

        try {
            $started = microtime(true);
            $resolved = $registry->resolve($network);
            $client = $resolved['client'];
            $tip = null;
            try {
                $tip = $client->tipHeight($network);
            } catch (Throwable) {
                $tip = null;
            }
            $ok = $tip !== null || $client->healthCheck();
            if (! $ok) {
                throw new \RuntimeException('Health check failed for '.$catalog->label($network));
            }
            $latencyMs = (int) round((microtime(true) - $started) * 1000);

            $meta = $row->meta ?? [];
            $health = $meta['network_health'] ?? [];
            $health[$network] = array_merge($health[$network] ?? [], [
                'status' => 'healthy',
                'provider' => $resolved['provider'],
                'client' => $resolved['client_key'],
                'endpoint' => $resolved['endpoint'],
                'auth_status' => $resolved['auth_status'],
                'last_poll_at' => now()->toIso8601String(),
                'last_success_at' => now()->toIso8601String(),
                'last_error' => null,
                'latency_ms' => $latencyMs,
                'tip_height' => $tip,
            ]);
            $meta['network_health'] = $health;
            $row->meta = $meta;
            $row->recordSuccess($latencyMs);

            $this->audit->log(auth()->id(), 'settings.blockchain.connection_test', $row, null, [
                'ok' => true,
                'network' => $network,
                'provider' => $resolved['provider'],
            ], $request->ip());

            $message = __('Connected to :network.', ['network' => $catalog->label($network)]);

            if ($wantsJson) {
                return response()->json([
                    'ok' => true,
                    'message' => $message,
                    'network' => $network,
                    'provider' => $resolved['provider'],
                    'tip_height' => $tip,
                    'latency_ms' => $latencyMs,
                ]);
            }

            return back()->with('status', $message);
        } catch (Throwable $e) {
            $row->recordFailure($e->getMessage());
            $this->audit->log(auth()->id(), 'settings.blockchain.connection_test', $row, null, [
                'ok' => false,
                'network' => $network,
            ], $request->ip());

            if ($wantsJson) {
                return response()->json([
                    'ok' => false,
                    'message' => $e->getMessage(),
                    'errors' => ['blockchain_test' => [$e->getMessage()]],
                ], 422);
            }

            return back()->withErrors(['blockchain_test' => $e->getMessage()]);
        }
    }
}
