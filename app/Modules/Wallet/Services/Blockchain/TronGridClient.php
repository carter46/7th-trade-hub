<?php

namespace App\Modules\Wallet\Services\Blockchain;

use RuntimeException;

class TronGridClient implements ChainExplorerClient
{
    public function __construct(private ExplorerHttp $http) {}

    public function networkKey(): string
    {
        return 'tron';
    }

    public function fetchIncoming(string $address, string $coin, ?string $network = null): array
    {
        $base = rtrim((string) config('crypto.trongrid_api'), '/');
        $headers = $this->headers();
        $coin = strtoupper($coin);
        $isUsdt = $coin === 'USDT';
        $allowedContract = $this->allowedContract($coin, $network ?: 'TRC20');

        if ($isUsdt) {
            $query = ['limit' => 50, 'only_to' => 'true'];
            if ($allowedContract) {
                $query['contract_address'] = $allowedContract;
            }
            $res = $this->http->get(
                "{$base}/v1/accounts/{$address}/transactions/trc20",
                $query,
                $headers,
                $this->http->maxRetries()
            );
        } else {
            $res = $this->http->get(
                "{$base}/v1/accounts/{$address}/transactions",
                ['limit' => 50, 'only_to' => 'true'],
                $headers,
                $this->http->maxRetries()
            );
        }

        if (! $res['ok']) {
            throw new RuntimeException($res['error'] ?? 'TronGrid failed');
        }

        $data = data_get($res['json'], 'data', []);
        if (! is_array($data)) {
            return [];
        }

        $tip = $this->tipHeight();
        $out = [];

        foreach ($data as $tx) {
            if (! is_array($tx)) {
                continue;
            }

            if ($isUsdt) {
                $hash = (string) ($tx['transaction_id'] ?? '');
                $to = (string) ($tx['to'] ?? '');
                if ($hash === '' || strcasecmp($to, $address) !== 0) {
                    continue;
                }

                $contract = (string) ($tx['token_info']['address'] ?? $tx['contract_address'] ?? '');
                if ($allowedContract && $contract !== '' && strcasecmp($contract, $allowedContract) !== 0) {
                    continue;
                }

                $decimals = (int) ($tx['token_info']['decimals'] ?? 6);
                $raw = (string) ($tx['value'] ?? '0');
                $amount = $this->scale($raw, $decimals);

                $blockHeight = null;
                if (isset($tx['block'])) {
                    $blockHeight = (int) $tx['block'];
                } elseif (isset($tx['block_number'])) {
                    $blockHeight = (int) $tx['block_number'];
                } else {
                    $blockHeight = $this->blockHeightForTx($hash);
                }

                $confirmations = 0;
                if ($blockHeight && $tip) {
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
                    'network' => $network ?: 'TRC20',
                    'token_contract' => $contract !== '' ? $contract : $allowedContract,
                    'raw' => $tx,
                ];
            } else {
                $hash = (string) ($tx['txID'] ?? '');
                if ($hash === '') {
                    continue;
                }
                $ret = $tx['ret'][0]['contractRet'] ?? null;
                if ($ret && $ret !== 'SUCCESS') {
                    continue;
                }
                $blockHeight = isset($tx['blockNumber']) ? (int) $tx['blockNumber'] : null;
                $confirmations = 0;
                if ($blockHeight && $tip) {
                    $confirmations = max(0, $tip - $blockHeight + 1);
                }
                $amount = 0.0;
                $contracts = $tx['raw_data']['contract'] ?? [];
                foreach ($contracts as $c) {
                    if (($c['type'] ?? '') === 'TransferContract') {
                        $amount = ((float) ($c['parameter']['value']['amount'] ?? 0)) / 1e6;
                        break;
                    }
                }
                if ($amount <= 0) {
                    continue;
                }

                $out[] = [
                    'tx_hash' => $hash,
                    'amount' => $amount,
                    'block_height' => $blockHeight,
                    'confirmations' => $confirmations,
                    'from_address' => null,
                    'to_address' => $address,
                    'coin' => $coin,
                    'network' => $network ?: 'TRC20',
                    'token_contract' => null,
                    'raw' => $tx,
                ];
            }
        }

        return $out;
    }

    public function tipHeight(?string $network = null): ?int
    {
        $base = rtrim((string) config('crypto.trongrid_api'), '/');
        $res = $this->http->get("{$base}/wallet/getnowblock", [], $this->headers(), $this->http->maxRetries());
        if (! $res['ok']) {
            return null;
        }
        $n = (int) data_get($res['json'], 'block_header.raw_data.number', 0);

        return $n > 0 ? $n : null;
    }

    public function healthCheck(): bool
    {
        return $this->tipHeight() !== null;
    }

    private function blockHeightForTx(string $hash): ?int
    {
        $base = rtrim((string) config('crypto.trongrid_api'), '/');
        $res = $this->http->postJson(
            "{$base}/wallet/gettransactioninfobyid",
            ['value' => $hash],
            $this->headers(),
            $this->http->maxRetries()
        );

        if (! ($res['ok'] ?? false)) {
            // Fallback: some ExplorerHttp setups only expose get()
            $res = $this->http->get(
                "{$base}/v1/transactions/{$hash}/info",
                [],
                $this->headers(),
                $this->http->maxRetries()
            );
            if (! $res['ok']) {
                return null;
            }
            $n = (int) data_get($res['json'], 'blockNumber', data_get($res['json'], 'block', 0));

            return $n > 0 ? $n : null;
        }

        $n = (int) data_get($res['json'], 'blockNumber', 0);

        return $n > 0 ? $n : null;
    }

    private function allowedContract(string $coin, ?string $network): ?string
    {
        $map = config('crypto.token_contracts.'.$coin, []);
        if (! is_array($map)) {
            return null;
        }
        $network = $network ?: 'TRC20';
        foreach ($map as $label => $contract) {
            if (strcasecmp((string) $label, $network) === 0 && is_string($contract) && $contract !== '') {
                return $contract;
            }
        }

        return null;
    }

    /** @return array<string, string> */
    private function headers(): array
    {
        $key = (string) ($this->http->monitoringProvider()->credential('trongrid_api_key') ?? '');

        return $key !== '' ? ['TRON-PRO-API-KEY' => $key] : [];
    }

    private function scale(string $value, int $decimals): float
    {
        if (! ctype_digit($value)) {
            return (float) $value / (10 ** $decimals);
        }
        $pad = str_pad($value, $decimals + 1, '0', STR_PAD_LEFT);
        $int = substr($pad, 0, -$decimals);
        $frac = substr($pad, -$decimals);

        return (float) ($int.'.'.$frac);
    }
}
