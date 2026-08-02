<?php

namespace App\Modules\Wallet\Services\Blockchain;

use App\Models\IntegrationProvider;

/**
 * Resolves ChainExplorerClient by monitor_provider + network_id via a map,
 * not a growing if (provider) tree.
 */
class ExplorerClientRegistry
{
    public function __construct(
        private ExplorerHttp $http,
        private MonitoredNetworkCatalog $catalog,
        private MempoolBitcoinClient $mempool,
        private EtherscanClient $etherscan,
        private TronGridClient $tron,
        private SolanaRpcClient $solana,
        private BlockchainComClient $blockchainCom,
    ) {}

    public function selectedProvider(): string
    {
        $meta = $this->http->monitoringProvider()->meta ?? [];
        $provider = (string) ($meta['monitor_provider'] ?? 'native');
        $catalog = config('crypto.monitor_providers', []);
        if (! isset($catalog[$provider]) || ! ($catalog[$provider]['enabled'] ?? false)) {
            return 'native';
        }

        return $provider;
    }

    /**
     * @return array{client: ChainExplorerClient, provider: string, client_key: string, network_id: string, endpoint: string, auth_status: string}
     */
    public function resolve(string $network): array
    {
        $networkId = $this->catalog->resolveId($network);
        $provider = $this->selectedProvider();
        $def = $this->catalog->definition($networkId);
        if ($def === null) {
            throw new \InvalidArgumentException("Unsupported network: {$network}");
        }

        $mapKey = $this->mapKey($provider, $networkId);
        $clientKey = $this->clientKeyFor($mapKey, $def);
        $client = $this->clientByKey($clientKey);
        $resolvedProvider = $this->effectiveProvider($provider, $networkId, $clientKey);

        return [
            'client' => $client,
            'provider' => $resolvedProvider,
            'client_key' => $clientKey,
            'network_id' => $networkId,
            'endpoint' => $this->endpointHost($clientKey),
            'auth_status' => $this->authStatus($clientKey),
        ];
    }

    public function clientForNetwork(string $network): ChainExplorerClient
    {
        return $this->resolve($network)['client'];
    }

    /**
     * Build resolution map key: "native:bitcoin", "blockchain_com:tron", etc.
     */
    private function mapKey(string $provider, string $networkId): string
    {
        // TRON always TronGrid regardless of selected provider.
        if ($networkId === 'tron') {
            return 'any:tron';
        }

        if ($provider === 'blockchain_com') {
            if ($this->catalog->supportsBlockchainCom($networkId)) {
                return 'blockchain_com:'.$networkId;
            }

            // EVM L2s / BSC: fall back to Native Etherscan.
            return 'native:'.$networkId;
        }

        return 'native:'.$networkId;
    }

    /**
     * @param  array<string, mixed>  $def
     */
    private function clientKeyFor(string $mapKey, array $def): string
    {
        static $map = [
            'any:tron' => 'trongrid',
            'native:bitcoin' => 'mempool',
            'native:ethereum' => 'etherscan',
            'native:bsc' => 'etherscan',
            'native:bep20' => 'etherscan',
            'native:polygon' => 'etherscan',
            'native:base' => 'etherscan',
            'native:arbitrum' => 'etherscan',
            'native:tron' => 'trongrid',
            'native:solana' => 'solana',
            'blockchain_com:bitcoin' => 'blockchain_com',
            'blockchain_com:ethereum' => 'blockchain_com',
            'blockchain_com:solana' => 'blockchain_com',
        ];

        if (isset($map[$mapKey])) {
            return $map[$mapKey];
        }

        return (string) ($def['native_client'] ?? 'etherscan');
    }

    private function clientByKey(string $clientKey): ChainExplorerClient
    {
        return match ($clientKey) {
            'mempool' => $this->mempool,
            'etherscan' => $this->etherscan,
            'trongrid' => $this->tron,
            'solana' => $this->solana,
            'blockchain_com' => $this->blockchainCom,
            default => throw new \InvalidArgumentException("Unknown explorer client: {$clientKey}"),
        };
    }

    private function effectiveProvider(string $selected, string $networkId, string $clientKey): string
    {
        if ($clientKey === 'blockchain_com') {
            return 'blockchain_com';
        }
        if ($selected === 'blockchain_com' && $clientKey !== 'blockchain_com') {
            return 'native';
        }

        return $selected === 'blockchain_com' ? 'blockchain_com' : 'native';
    }

    private function endpointHost(string $clientKey): string
    {
        $url = match ($clientKey) {
            'mempool' => (string) config('crypto.mempool_api'),
            'etherscan' => (string) config('crypto.etherscan_api'),
            'trongrid' => (string) config('crypto.trongrid_api'),
            'solana' => (string) (IntegrationProvider::forProvider(IntegrationProvider::BLOCKCHAIN_MONITORING)->credential('solana_rpc_url')
                ?: config('crypto.solana_rpc')),
            'blockchain_com' => (string) (
                IntegrationProvider::forProvider(IntegrationProvider::BLOCKCHAIN_MONITORING)->credential('blockchain_com_base_url')
                ?: config('crypto.blockchain_com.base_url')
            ),
            default => '',
        };

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : $url;
    }

    private function authStatus(string $clientKey): string
    {
        $row = $this->http->monitoringProvider();

        return match ($clientKey) {
            'mempool' => 'ok',
            'etherscan' => filled($row->credential('etherscan_api_key')) ? 'ok' : 'missing_key',
            'trongrid' => filled($row->credential('trongrid_api_key')) ? 'ok' : 'missing_key',
            'solana' => 'ok',
            'blockchain_com' => filled($row->credential('blockchain_com_api_key')) ? 'ok' : 'missing_key',
            default => 'unknown',
        };
    }
}
