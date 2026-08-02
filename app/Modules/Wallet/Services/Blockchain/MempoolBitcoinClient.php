<?php

namespace App\Modules\Wallet\Services\Blockchain;

use RuntimeException;

class MempoolBitcoinClient implements ChainExplorerClient
{
    public function __construct(private ExplorerHttp $http) {}

    public function networkKey(): string
    {
        return 'bitcoin';
    }

    public function fetchIncoming(string $address, string $coin, ?string $network = null): array
    {
        $base = rtrim((string) config('crypto.mempool_api'), '/');
        $tip = $this->tipHeight();
        $res = $this->http->get("{$base}/address/{$address}/txs", [], [], $this->http->maxRetries());
        if (! $res['ok'] || ! is_array($res['json'])) {
            throw new RuntimeException($res['error'] ?? 'mempool.space failed');
        }

        $out = [];
        foreach ($res['json'] as $tx) {
            if (! is_array($tx)) {
                continue;
            }
            $txid = (string) ($tx['txid'] ?? '');
            if ($txid === '') {
                continue;
            }

            $amountSats = 0;
            foreach ($tx['vout'] ?? [] as $vout) {
                if (! is_array($vout)) {
                    continue;
                }
                $addr = $vout['scriptpubkey_address'] ?? null;
                if ($addr === $address) {
                    $amountSats += (int) ($vout['value'] ?? 0);
                }
            }

            if ($amountSats <= 0) {
                continue;
            }

            $blockHeight = isset($tx['status']['block_height']) ? (int) $tx['status']['block_height'] : null;
            $confirmed = (bool) ($tx['status']['confirmed'] ?? false);
            $confirmations = 0;
            if ($confirmed && $blockHeight && $tip) {
                $confirmations = max(0, $tip - $blockHeight + 1);
            }

            $from = null;
            foreach ($tx['vin'] ?? [] as $vin) {
                $prev = $vin['prevout']['scriptpubkey_address'] ?? null;
                if (is_string($prev) && $prev !== '') {
                    $from = $prev;
                    break;
                }
            }

            $out[] = [
                'tx_hash' => $txid,
                'amount' => $amountSats / 1e8,
                'block_height' => $blockHeight,
                'confirmations' => $confirmations,
                'from_address' => $from,
                'to_address' => $address,
                'coin' => strtoupper($coin),
                'network' => $network ?: 'Bitcoin',
                'token_contract' => null,
                'raw' => $tx,
            ];
        }

        return $out;
    }

    public function fetchBalance(string $address, string $coin, ?string $network = null): float
    {
        $base = rtrim((string) config('crypto.mempool_api'), '/');
        $res = $this->http->get("{$base}/address/{$address}", [], [], $this->http->maxRetries());
        if (! $res['ok'] || ! is_array($res['json'])) {
            throw new RuntimeException($res['error'] ?? 'mempool.space balance failed');
        }

        $chain = $res['json']['chain_stats'] ?? [];
        $mempool = $res['json']['mempool_stats'] ?? [];
        $funded = (int) ($chain['funded_txo_sum'] ?? 0) + (int) ($mempool['funded_txo_sum'] ?? 0);
        $spent = (int) ($chain['spent_txo_sum'] ?? 0) + (int) ($mempool['spent_txo_sum'] ?? 0);

        return max(0, $funded - $spent) / 1e8;
    }

    public function tipHeight(?string $network = null): ?int
    {
        $base = rtrim((string) config('crypto.mempool_api'), '/');
        $res = $this->http->get("{$base}/blocks/tip/height", [], [], $this->http->maxRetries());
        if (! $res['ok']) {
            return null;
        }
        $height = (int) trim($res['body']);

        return $height > 0 ? $height : null;
    }

    public function healthCheck(): bool
    {
        return $this->tipHeight() !== null;
    }
}
