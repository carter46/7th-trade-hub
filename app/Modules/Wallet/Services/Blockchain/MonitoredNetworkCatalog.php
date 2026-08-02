<?php

namespace App\Modules\Wallet\Services\Blockchain;

/**
 * Canonical network_id helpers driven by config('crypto.monitored_networks').
 */
class MonitoredNetworkCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        $rows = config('crypto.monitored_networks', []);

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return list<string>
     */
    public function ids(): array
    {
        return array_keys($this->all());
    }

    public function label(string $networkId): string
    {
        $row = $this->all()[$networkId] ?? null;

        return is_array($row) ? (string) ($row['label'] ?? $networkId) : $networkId;
    }

    /**
     * Resolve wallet/order network label to monitored network_id.
     */
    public function resolveId(string $network): string
    {
        $map = config('crypto.network_client', []);
        foreach ($map as $label => $networkId) {
            if (strcasecmp((string) $label, $network) === 0) {
                return strtolower((string) $networkId);
            }
        }

        $key = strtolower(trim($network));
        foreach ($this->all() as $id => $meta) {
            if (strcasecmp($id, $key) === 0) {
                return $id;
            }
            foreach ($meta['aliases'] ?? [] as $alias) {
                if (strcasecmp((string) $alias, $network) === 0 || strcasecmp((string) $alias, $key) === 0) {
                    return $id;
                }
            }
        }

        $legacy = [
            'btc' => 'bitcoin',
            'eth' => 'ethereum',
            'erc20' => 'ethereum',
            'trc20' => 'tron',
            'sol' => 'solana',
            'matic' => 'polygon',
            'arb' => 'arbitrum',
        ];

        return $legacy[$key] ?? $key;
    }

    public function definition(string $networkId): ?array
    {
        $row = $this->all()[$networkId] ?? null;

        return is_array($row) ? $row : null;
    }

    public function supportsBlockchainCom(string $networkId): bool
    {
        $def = $this->definition($networkId);

        return (bool) ($def['blockchain_com'] ?? false);
    }

    /**
     * Human label for Settings “Provider” column.
     */
    public function displayProvider(string $networkId, string $resolvedProvider, string $clientKey): string
    {
        if ($clientKey === 'mempool') {
            return 'Public explorer';
        }
        if ($resolvedProvider === 'blockchain_com' && $this->supportsBlockchainCom($networkId) && in_array($clientKey, ['blockchain_com'], true)) {
            return 'Blockchain.com';
        }
        if ($resolvedProvider === 'blockchain_com' && $clientKey !== 'blockchain_com') {
            if ($networkId === 'tron') {
                return 'Native (TRON)';
            }

            return 'Native (fallback)';
        }

        $def = $this->definition($networkId);

        return (string) ($def['ui_provider_native'] ?? 'Native');
    }
}
