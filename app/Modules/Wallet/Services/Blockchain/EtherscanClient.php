<?php

namespace App\Modules\Wallet\Services\Blockchain;

use RuntimeException;

class EtherscanClient implements ChainExplorerClient
{
    public function __construct(private ExplorerHttp $http) {}

    public function networkKey(): string
    {
        return 'ethereum';
    }

    public function fetchIncoming(string $address, string $coin, ?string $network = null): array
    {
        $apiKey = (string) ($this->http->monitoringProvider()->credential('etherscan_api_key') ?? '');
        if ($apiKey === '') {
            throw new RuntimeException('Etherscan API key missing in Admin Settings.');
        }

        $coin = strtoupper($coin);
        $chain = $this->resolveChain($network);
        $isNative = $this->isNativeCoin($coin, $chain);
        $allowedContract = $this->allowedContract($coin, $network);

        $query = [
            'chainid' => $chain['chainid'],
            'module' => 'account',
            'action' => $isNative ? 'txlist' : 'tokentx',
            'address' => $address,
            'page' => 1,
            'offset' => 50,
            'sort' => 'desc',
            'apikey' => $apiKey,
        ];

        if (! $isNative && $allowedContract) {
            $query['contractaddress'] = $allowedContract;
        }

        $base = $this->apiBase();
        $res = $this->http->get($base, $query, [], $this->http->maxRetries());
        if (! $res['ok']) {
            throw new RuntimeException($res['error'] ?? 'Etherscan failed');
        }

        $status = (string) data_get($res['json'], 'status', '0');
        $result = data_get($res['json'], 'result', []);
        if ($status !== '1' || ! is_array($result)) {
            if (is_string($result) && str_contains(strtolower($result), 'no transaction')) {
                return [];
            }
            if ($result === [] || $result === null) {
                return [];
            }
            throw new RuntimeException(is_string($result) ? $result : 'Etherscan returned error');
        }

        $tip = $this->tipHeightForChain($chain['chainid'], $apiKey);
        $out = [];
        foreach ($result as $tx) {
            if (! is_array($tx)) {
                continue;
            }
            $to = strtolower((string) ($tx['to'] ?? ''));
            if ($to !== strtolower($address)) {
                continue;
            }

            $hash = (string) ($tx['hash'] ?? '');
            if ($hash === '') {
                continue;
            }

            $contract = null;
            if (! $isNative) {
                $contract = strtolower((string) ($tx['contractAddress'] ?? ''));
                if ($allowedContract && $contract !== '' && strcasecmp($contract, $allowedContract) !== 0) {
                    continue;
                }
                if ($allowedContract === null) {
                    $symbol = strtoupper((string) ($tx['tokenSymbol'] ?? ''));
                    if ($symbol !== '' && $symbol !== $coin) {
                        continue;
                    }
                }
            }

            $decimals = $isNative ? 18 : (int) ($tx['tokenDecimal'] ?? 18);
            $rawValue = (string) ($tx['value'] ?? '0');
            $amount = $this->fromWei($rawValue, $decimals);
            if ($amount <= 0) {
                continue;
            }

            $blockHeight = isset($tx['blockNumber']) ? (int) $tx['blockNumber'] : null;
            $confirmations = (int) ($tx['confirmations'] ?? 0);
            if ($confirmations <= 0 && $blockHeight && $tip) {
                $confirmations = max(0, $tip - $blockHeight + 1);
            }

            $out[] = [
                'tx_hash' => $hash,
                'amount' => $amount,
                'block_height' => $blockHeight,
                'confirmations' => $confirmations,
                'from_address' => $tx['from'] ?? null,
                'to_address' => $address,
                'coin' => $coin,
                'network' => app(\App\Modules\Wallet\Services\NetworkRegistry::class)->resolveId($network ?: 'ethereum'),
                'token_contract' => $contract ?: $allowedContract,
                'raw' => $tx,
            ];
        }

        return $out;
    }

    public function fetchBalance(string $address, string $coin, ?string $network = null): float
    {
        $apiKey = (string) ($this->http->monitoringProvider()->credential('etherscan_api_key') ?? '');
        if ($apiKey === '') {
            throw new RuntimeException('Etherscan API key missing in Admin Settings.');
        }

        $coin = strtoupper($coin);
        $chain = $this->resolveChain($network);
        $isNative = $this->isNativeCoin($coin, $chain);

        if ($isNative) {
            $res = $this->http->get($this->apiBase(), [
                'chainid' => $chain['chainid'],
                'module' => 'account',
                'action' => 'balance',
                'address' => $address,
                'tag' => 'latest',
                'apikey' => $apiKey,
            ], [], $this->http->maxRetries());

            if (! $res['ok']) {
                throw new RuntimeException($res['error'] ?? 'Etherscan balance failed');
            }

            $status = (string) data_get($res['json'], 'status', '0');
            $result = data_get($res['json'], 'result', '0');
            if ($status !== '1' && ! ctype_digit((string) $result)) {
                throw new RuntimeException(is_string($result) ? $result : 'Etherscan balance error');
            }

            return $this->fromWei((string) $result, 18);
        }

        $contract = $this->allowedContract($coin, $network);
        if ($contract === null || $contract === '') {
            throw new RuntimeException("No token contract configured for {$coin} on ".($network ?: 'EVM'));
        }

        $res = $this->http->get($this->apiBase(), [
            'chainid' => $chain['chainid'],
            'module' => 'account',
            'action' => 'tokenbalance',
            'contractaddress' => $contract,
            'address' => $address,
            'tag' => 'latest',
            'apikey' => $apiKey,
        ], [], $this->http->maxRetries());

        if (! $res['ok']) {
            throw new RuntimeException($res['error'] ?? 'Etherscan token balance failed');
        }

        $status = (string) data_get($res['json'], 'status', '0');
        $result = data_get($res['json'], 'result', '0');
        if ($status !== '1' && ! ctype_digit((string) $result)) {
            throw new RuntimeException(is_string($result) ? $result : 'Etherscan token balance error');
        }

        $decimals = $this->tokenDecimals($coin);

        return $this->fromWei((string) $result, $decimals);
    }

    public function tipHeight(?string $network = null): ?int
    {
        $apiKey = (string) ($this->http->monitoringProvider()->credential('etherscan_api_key') ?? '');
        if ($apiKey === '') {
            return null;
        }
        $chain = $this->resolveChain($network);

        return $this->tipHeightForChain($chain['chainid'], $apiKey);
    }

    public function healthCheck(): bool
    {
        return $this->tipHeight() !== null;
    }

    private function tokenDecimals(string $coin): int
    {
        return match (strtoupper($coin)) {
            'USDT', 'USDC' => 6,
            default => 18,
        };
    }

    /**
     * @return array{chainid: int, native: list<string>}
     */
    private function resolveChain(?string $network): array
    {
        $map = config('crypto.evm_chains', []);
        $id = null;
        if (is_string($network) && $network !== '') {
            try {
                $id = app(\App\Modules\Wallet\Services\NetworkRegistry::class)->resolveId($network);
            } catch (\Throwable) {
                $id = strtolower(trim($network));
            }
            foreach ($map as $label => $meta) {
                if (! is_array($meta)) {
                    continue;
                }
                if (strcasecmp((string) $label, $network) === 0
                    || ($id && strcasecmp((string) $label, $id) === 0)) {
                    return [
                        'chainid' => (int) ($meta['chainid'] ?? 1),
                        'native' => array_map('strtoupper', $meta['native'] ?? ['ETH']),
                    ];
                }
            }
        }

        return ['chainid' => 1, 'native' => ['ETH']];
    }

    /**
     * @param  array{chainid: int, native: list<string>}  $chain
     */
    private function isNativeCoin(string $coin, array $chain): bool
    {
        return in_array($coin, $chain['native'], true);
    }

    private function apiBase(): string
    {
        $v2 = (string) config('crypto.etherscan_api', 'https://api.etherscan.io/v2/api');
        if (str_contains($v2, '/v2/')) {
            return rtrim($v2, '/');
        }

        return rtrim((string) config('crypto.etherscan_api_v1', 'https://api.etherscan.io/api'), '/');
    }

    private function tipHeightForChain(int $chainId, string $apiKey): ?int
    {
        $res = $this->http->get($this->apiBase(), [
            'chainid' => $chainId,
            'module' => 'proxy',
            'action' => 'eth_blockNumber',
            'apikey' => $apiKey,
        ], [], $this->http->maxRetries());

        if (! $res['ok']) {
            return null;
        }
        $hex = (string) data_get($res['json'], 'result', '');
        if (! str_starts_with($hex, '0x')) {
            return null;
        }

        return (int) hexdec($hex);
    }

    private function allowedContract(string $coin, ?string $network): ?string
    {
        if ($network === null || $network === '') {
            return null;
        }
        $contract = app(\App\Modules\Wallet\Services\NetworkRegistry::class)->tokenContract($coin, $network);

        return is_string($contract) && $contract !== '' ? strtolower($contract) : null;
    }

    private function fromWei(string $value, int $decimals): float
    {
        if (! ctype_digit($value)) {
            return (float) $value;
        }
        if ($decimals <= 0) {
            return (float) $value;
        }
        $pad = str_pad($value, $decimals + 1, '0', STR_PAD_LEFT);
        $int = substr($pad, 0, -$decimals);
        $frac = substr($pad, -$decimals);

        return (float) ($int.'.'.$frac);
    }
}
