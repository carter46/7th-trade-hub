<?php

namespace App\Modules\Wallet\Services\Blockchain;

use RuntimeException;

class SolanaRpcClient implements ChainExplorerClient
{
    public function __construct(private ExplorerHttp $http) {}

    public function networkKey(): string
    {
        return 'solana';
    }

    public function fetchIncoming(string $address, string $coin, ?string $network = null): array
    {
        $rpc = $this->rpcUrl();
        $coin = strtoupper($coin);
        $mint = $this->allowedMint($coin, $network ?: 'Solana');
        $isSpl = $mint !== null;

        $sigRes = $this->rpc($rpc, 'getSignaturesForAddress', [
            $address,
            ['limit' => 20],
        ]);

        $sigs = $sigRes['json']['result'] ?? null;
        if (! is_array($sigs)) {
            throw new RuntimeException($sigRes['error'] ?? 'Solana signatures failed');
        }

        $tip = $this->tipHeight();
        $out = [];

        foreach ($sigs as $row) {
            if (! is_array($row)) {
                continue;
            }
            $signature = (string) ($row['signature'] ?? '');
            if ($signature === '') {
                continue;
            }

            $txRes = $this->rpc($rpc, 'getTransaction', [
                $signature,
                ['encoding' => 'jsonParsed', 'maxSupportedTransactionVersion' => 0],
            ]);
            $tx = $txRes['json']['result'] ?? null;
            if (! is_array($tx)) {
                continue;
            }

            $meta = $tx['meta'] ?? [];
            if (($meta['err'] ?? null) !== null) {
                continue;
            }

            $amount = 0.0;
            $tokenContract = null;

            if ($isSpl) {
                $delta = $this->splDeltaForOwner($meta, $address, $mint);
                if ($delta <= 0) {
                    continue;
                }
                $amount = $delta;
                $tokenContract = $mint;
            } else {
                $pre = $meta['preBalances'] ?? [];
                $post = $meta['postBalances'] ?? [];
                $keys = $tx['transaction']['message']['accountKeys'] ?? [];
                foreach ($keys as $i => $key) {
                    $pubkey = is_array($key) ? ($key['pubkey'] ?? '') : (string) $key;
                    if ($pubkey !== $address) {
                        continue;
                    }
                    $nativeDelta = ((int) ($post[$i] ?? 0)) - ((int) ($pre[$i] ?? 0));
                    if ($nativeDelta > 0) {
                        $amount = $nativeDelta / 1e9;
                    }
                }
                if ($amount <= 0) {
                    continue;
                }
            }

            $slot = isset($tx['slot']) ? (int) $tx['slot'] : null;
            $confirmations = 0;
            if ($slot && $tip) {
                $confirmations = max(0, $tip - $slot + 1);
            }

            $out[] = [
                'tx_hash' => $signature,
                'amount' => $amount,
                'block_height' => $slot,
                'confirmations' => $confirmations,
                'from_address' => null,
                'to_address' => $address,
                'coin' => $coin,
                'network' => $network ?: 'Solana',
                'token_contract' => $tokenContract,
                'raw' => $tx,
            ];
        }

        return $out;
    }

    public function fetchBalance(string $address, string $coin, ?string $network = null): float
    {
        $rpc = $this->rpcUrl();
        $coin = strtoupper($coin);
        $mint = $this->allowedMint($coin, $network ?: 'Solana');

        if ($mint === null) {
            $res = $this->rpc($rpc, 'getBalance', [$address]);
            if (! ($res['ok'] ?? false)) {
                throw new RuntimeException($res['error'] ?? 'Solana getBalance failed');
            }
            $lamports = (int) data_get($res['json'], 'result.value', 0);

            return $lamports / 1e9;
        }

        $res = $this->rpc($rpc, 'getTokenAccountsByOwner', [
            $address,
            ['mint' => $mint],
            ['encoding' => 'jsonParsed'],
        ]);

        if (! ($res['ok'] ?? false)) {
            throw new RuntimeException($res['error'] ?? 'Solana token balance failed');
        }

        $accounts = data_get($res['json'], 'result.value', []);
        if (! is_array($accounts)) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($accounts as $account) {
            $ui = data_get($account, 'account.data.parsed.info.tokenAmount.uiAmount');
            if ($ui !== null) {
                $total += (float) $ui;
            }
        }

        return $total;
    }

    public function tipHeight(?string $network = null): ?int
    {
        $res = $this->rpc($this->rpcUrl(), 'getSlot', []);
        $slot = $res['json']['result'] ?? null;

        return is_numeric($slot) ? (int) $slot : null;
    }

    public function healthCheck(): bool
    {
        return $this->tipHeight() !== null;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function splDeltaForOwner(array $meta, string $owner, string $mint): float
    {
        $preMap = $this->tokenBalanceMap($meta['preTokenBalances'] ?? [], $owner, $mint);
        $postMap = $this->tokenBalanceMap($meta['postTokenBalances'] ?? [], $owner, $mint);

        $delta = 0.0;
        foreach (array_unique(array_merge(array_keys($preMap), array_keys($postMap))) as $idx) {
            $delta += ($postMap[$idx] ?? 0.0) - ($preMap[$idx] ?? 0.0);
        }

        return $delta;
    }

    /**
     * @param  list<mixed>  $balances
     * @return array<int|string, float>
     */
    private function tokenBalanceMap(array $balances, string $owner, string $mint): array
    {
        $map = [];
        foreach ($balances as $bal) {
            if (! is_array($bal)) {
                continue;
            }
            if (strcasecmp((string) ($bal['owner'] ?? ''), $owner) !== 0) {
                continue;
            }
            if (strcasecmp((string) ($bal['mint'] ?? ''), $mint) !== 0) {
                continue;
            }
            $ui = $bal['uiTokenAmount']['uiAmount'] ?? null;
            if ($ui === null) {
                $amount = (string) ($bal['uiTokenAmount']['amount'] ?? '0');
                $decimals = (int) ($bal['uiTokenAmount']['decimals'] ?? 0);
                $ui = $decimals > 0 ? ((float) $amount) / (10 ** $decimals) : (float) $amount;
            }
            $key = $bal['accountIndex'] ?? count($map);
            $map[$key] = (float) $ui;
        }

        return $map;
    }

    private function allowedMint(string $coin, string $network): ?string
    {
        if (in_array($coin, ['SOL'], true)) {
            return null;
        }
        $map = config('crypto.token_contracts.'.$coin, []);
        if (! is_array($map)) {
            return null;
        }
        foreach ($map as $label => $mint) {
            if (strcasecmp((string) $label, $network) === 0 && is_string($mint) && $mint !== '') {
                return $mint;
            }
        }

        return $map['Solana'] ?? null;
    }

    private function rpcUrl(): string
    {
        $custom = (string) ($this->http->monitoringProvider()->credential('solana_rpc_url') ?? '');

        return $custom !== '' ? $custom : (string) config('crypto.solana_rpc');
    }

    /**
     * @param  list<mixed>  $params
     * @return array{ok: bool, status: int, json: mixed, body: string, attempts: int, error: ?string}
     */
    private function rpc(string $url, string $method, array $params): array
    {
        $max = $this->http->maxRetries();
        $last = null;
        for ($i = 1; $i <= $max; $i++) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(15)
                    ->acceptJson()
                    ->post($url, [
                        'jsonrpc' => '2.0',
                        'id' => 1,
                        'method' => $method,
                        'params' => $params,
                    ]);
                $last = [
                    'ok' => $response->successful() && ! isset($response->json()['error']),
                    'status' => $response->status(),
                    'json' => $response->json(),
                    'body' => $response->body(),
                    'attempts' => $i,
                    'error' => data_get($response->json(), 'error.message'),
                ];
                if ($last['ok']) {
                    return $last;
                }
            } catch (\Throwable $e) {
                $last = [
                    'ok' => false,
                    'status' => 0,
                    'json' => null,
                    'body' => '',
                    'attempts' => $i,
                    'error' => $e->getMessage(),
                ];
            }
            if ($i < $max) {
                usleep(250_000 * $i);
            }
        }

        return $last ?? [
            'ok' => false,
            'status' => 0,
            'json' => null,
            'body' => '',
            'attempts' => $max,
            'error' => 'Solana RPC failed',
        ];
    }
}
