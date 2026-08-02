<?php

namespace App\Modules\Wallet\Services;

use App\Models\ExchangeRate;
use App\Modules\Wallet\Services\Blockchain\MonitoredNetworkCatalog;
use Illuminate\Support\Facades\Schema;

/**
 * Sole infrastructure registry for blockchains + catalog-driven coin→network rules.
 * Persist canonical IDs; render labels via label().
 */
class NetworkRegistry
{
    public function __construct(
        private MonitoredNetworkCatalog $catalog,
    ) {}

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->catalog->all();
    }

    /**
     * @return list<string>
     */
    public function ids(): array
    {
        return $this->catalog->ids();
    }

    public function label(string $networkId): string
    {
        $id = $this->normalizeId($networkId);

        return $this->catalog->label($id);
    }

    public function resolveId(string $input): string
    {
        $resolved = $this->catalog->resolveId($input);

        return $this->normalizeId($resolved);
    }

    public function sameNetwork(string $a, string $b): bool
    {
        return $this->resolveId($a) === $this->resolveId($b);
    }

    /**
     * All lowercase storage strings that map to the same canonical network ID
     * (ID + aliases + legacy labels). Use for SQL WHERE IN during migration.
     *
     * @return list<string>
     */
    public function storageVariants(string $network): array
    {
        $id = $this->resolveId($network);
        $variants = [$id];

        $def = $this->catalog->definition($id) ?? [];
        foreach ($def['aliases'] ?? [] as $alias) {
            if (is_string($alias) && $alias !== '') {
                $variants[] = strtolower($alias);
            }
        }

        foreach (config('crypto.network_client', []) as $label => $mapped) {
            if ($this->normalizeId((string) $mapped) === $id && is_string($label) && $label !== '') {
                $variants[] = strtolower($label);
            }
        }

        // Residual legacy spellings.
        $extra = match ($id) {
            'bitcoin' => ['btc', 'bitcoin'],
            'ethereum' => ['eth', 'erc20', 'ethereum'],
            'tron' => ['trc20', 'tron'],
            'bsc' => ['bep20', 'bsc', 'bnb'],
            'polygon' => ['matic', 'polygon'],
            'arbitrum' => ['arb', 'arbitrum', 'arbitrum one'],
            'solana' => ['sol', 'solana'],
            'base' => ['base'],
            default => [],
        };
        foreach ($extra as $v) {
            $variants[] = $v;
        }

        return array_values(array_unique($variants));
    }

    public function defaultConfirmations(string $networkId): int
    {
        $id = $this->resolveId($networkId);
        $map = config('crypto.default_confirmations', []);
        if (isset($map[$id])) {
            return max(1, (int) $map[$id]);
        }
        // Legacy label keys during transition.
        foreach ($map as $key => $count) {
            if ($this->resolveId((string) $key) === $id) {
                return max(1, (int) $count);
            }
        }

        return 12;
    }

    public function explorerName(string $networkId): string
    {
        $id = $this->resolveId($networkId);
        $def = $this->catalog->definition($id) ?? [];
        $client = (string) ($def['native_client'] ?? '');

        return match ($client) {
            'mempool' => 'Mempool / public explorer',
            'etherscan' => match ($id) {
                'bsc' => 'BscScan (Etherscan API)',
                'polygon' => 'Polygonscan (Etherscan API)',
                'base' => 'Basescan (Etherscan API)',
                'arbitrum' => 'Arbiscan (Etherscan API)',
                default => 'Etherscan',
            },
            'trongrid' => 'TronGrid',
            'solana' => 'Solana RPC',
            default => (string) ($def['ui_provider_native'] ?? 'Not configured'),
        };
    }

    public function isMonitorable(string $networkId): bool
    {
        $id = $this->resolveId($networkId);
        $def = $this->catalog->definition($id);
        if (! is_array($def)) {
            return false;
        }
        $client = (string) ($def['native_client'] ?? '');

        return $client !== '';
    }

    /**
     * Soft defaults when adding a catalog coin — never a runtime ceiling.
     *
     * @return list<string>
     */
    public function suggestDefaultsForAsset(string $symbol): array
    {
        $map = config('crypto.suggest_network_ids_by_coin', config('crypto.network_ids_by_coin', []));
        $list = $map[strtoupper(trim($symbol))] ?? [];
        $ids = [];
        foreach ($list as $id) {
            if (! is_string($id) || $id === '') {
                continue;
            }
            $normalized = $this->normalizeId($id);
            if ($this->catalog->definition($normalized)) {
                $ids[] = $normalized;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Allowed network IDs from Coin Catalog only (business SoT).
     *
     * @return list<string>
     */
    public function idsForCoin(string $symbol): array
    {
        $symbol = strtoupper(trim($symbol));
        if ($symbol === '' || ! Schema::hasTable('exchange_rates')) {
            return [];
        }

        $row = ExchangeRate::query()
            ->whereRaw('UPPER(asset) = ?', [$symbol])
            ->first();

        if (! $row) {
            return [];
        }

        $ids = $row->resolvedNetworkIds();
        $out = [];
        foreach ($ids as $id) {
            $normalized = $this->normalizeId($id);
            if ($this->catalog->definition($normalized)) {
                $out[] = $normalized;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Monitorable allowed networks for a coin.
     *
     * @return list<string>
     */
    public function monitorableIdsForCoin(string $symbol): array
    {
        return array_values(array_filter(
            $this->idsForCoin($symbol),
            fn (string $id) => $this->isMonitorable($id)
        ));
    }

    public function canEnableForOtc(string $symbol): bool
    {
        return $this->monitorableIdsForCoin($symbol) !== [];
    }

    /**
     * @return list<array{id: string, label: string, explorer: string, monitorable: bool}>
     */
    public function checkboxOptions(): array
    {
        $rows = [];
        foreach ($this->ids() as $id) {
            $rows[] = [
                'id' => $id,
                'label' => $this->label($id),
                'explorer' => $this->explorerName($id),
                'monitorable' => $this->isMonitorable($id),
            ];
        }

        return $rows;
    }

    /**
     * Select options for a coin (catalog ∩ monitorable).
     *
     * @return list<array{id: string, label: string}>
     */
    public function optionsForCoin(string $symbol): array
    {
        $rows = [];
        foreach ($this->monitorableIdsForCoin($symbol) as $id) {
            $rows[] = [
                'id' => $id,
                'label' => $this->label($id),
            ];
        }

        return $rows;
    }

    /**
     * Catalog symbols that allow this network ID.
     *
     * @return list<string>
     */
    public function coinsUsingNetwork(string $networkId): array
    {
        $id = $this->resolveId($networkId);
        if (! Schema::hasTable('exchange_rates')) {
            return [];
        }

        return ExchangeRate::query()
            ->orderBy('sort_order')
            ->orderBy('asset')
            ->get()
            ->filter(function (ExchangeRate $rate) use ($id) {
                $ids = array_map(fn ($n) => $this->normalizeId((string) $n), $rate->resolvedNetworkIds());

                return in_array($id, $ids, true);
            })
            ->map(fn (ExchangeRate $rate) => strtoupper((string) $rate->asset))
            ->filter()
            ->values()
            ->all();
    }

    public function preferredNetworkId(string $symbol): ?string
    {
        $symbol = strtoupper(trim($symbol));
        if (! Schema::hasTable('exchange_rates')
            || ! Schema::hasColumn('exchange_rates', 'preferred_network_id')) {
            return null;
        }

        $row = ExchangeRate::query()
            ->whereRaw('UPPER(asset) = ?', [$symbol])
            ->first();

        if (! $row || ! filled($row->preferred_network_id)) {
            return null;
        }

        $preferred = $this->normalizeId((string) $row->preferred_network_id);
        $allowed = $this->monitorableIdsForCoin($symbol);

        return in_array($preferred, $allowed, true) ? $preferred : null;
    }

    public function tokenContract(string $coin, string $network): ?string
    {
        $map = config('crypto.token_contracts.'.strtoupper($coin), []);
        if (! is_array($map)) {
            return null;
        }
        $id = $this->resolveId($network);
        $contract = $map[$id] ?? null;
        if (is_string($contract) && $contract !== '') {
            return $contract;
        }
        // Legacy label keys.
        foreach ($map as $key => $value) {
            if ($this->resolveId((string) $key) === $id && is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function normalizeId(string $id): string
    {
        $id = strtolower(trim($id));

        return match ($id) {
            'bep20', 'bnb', 'binance-smart-chain', 'bnb smart chain' => 'bsc',
            default => $id,
        };
    }
}
