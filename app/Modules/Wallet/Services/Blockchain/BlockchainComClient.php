<?php

namespace App\Modules\Wallet\Services\Blockchain;

use RuntimeException;

/**
 * Blockchain.com Explorer Gateway adapter (BTC / ETH / SOL).
 *
 * Auth: X-Explorer-Auth-Key. ERC-20 / SPL transfer feeds are not available on
 * the gateway, so those coins fall back to Native Etherscan / Solana RPC.
 */
class BlockchainComClient implements ChainExplorerClient
{
    public function __construct(
        private ExplorerHttp $http,
        private EtherscanClient $etherscan,
        private SolanaRpcClient $solana,
        private MonitoredNetworkCatalog $catalog,
    ) {}

    public function networkKey(): string
    {
        return 'blockchain_com';
    }

    public function fetchIncoming(string $address, string $coin, ?string $network = null): array
    {
        $networkId = $this->catalog->resolveId($network ?: 'Bitcoin');
        $coin = strtoupper($coin);

        return match ($networkId) {
            'bitcoin' => $this->fetchBitcoin($address, $coin, $network ?: 'Bitcoin'),
            'ethereum' => $this->fetchEthereum($address, $coin, $network ?: 'Ethereum'),
            'solana' => $this->fetchSolana($address, $coin, $network ?: 'Solana'),
            default => throw new RuntimeException("Blockchain.com does not support network: {$networkId}"),
        };
    }

    public function fetchBalance(string $address, string $coin, ?string $network = null): float
    {
        $networkId = $this->catalog->resolveId($network ?: 'Bitcoin');
        $coin = strtoupper($coin);

        return match ($networkId) {
            'bitcoin' => $this->balanceBitcoin($address),
            'ethereum' => $coin === 'ETH'
                ? $this->balanceEthereum($address)
                : $this->etherscan->fetchBalance($address, $coin, $network ?: 'Ethereum'),
            'solana' => $coin === 'SOL'
                ? $this->balanceSolana($address)
                : $this->solana->fetchBalance($address, $coin, $network ?: 'Solana'),
            default => throw new RuntimeException("Blockchain.com does not support network: {$networkId}"),
        };
    }

    public function tipHeight(?string $network = null): ?int
    {
        $networkId = $this->catalog->resolveId($network ?: 'Bitcoin');

        return match ($networkId) {
            'bitcoin' => $this->tipForPath('/btc/blocks', []),
            'ethereum' => $this->tipForPath('/eth/blocks', ['network' => 'mainnet']),
            'solana' => $this->tipForPath('/sol/blocks', ['network' => 'mainnet']),
            default => null,
        };
    }

    public function healthCheck(): bool
    {
        return $this->tipHeight('Bitcoin') !== null
            || $this->probeAddress('btc');
    }

    private function balanceBitcoin(string $address): float
    {
        try {
            $json = $this->post('/btc/address', ['address' => $address]);
            foreach (['balance', 'final_balance', 'confirmed'] as $key) {
                if (isset($json[$key]) && is_numeric($json[$key])) {
                    $val = (float) $json[$key];

                    // Gateway may return sats or BTC.
                    return $val > 1000 ? $val / 1e8 : $val;
                }
            }
        } catch (\Throwable) {
            // Fall through to mempool-style native path via address payload failure.
        }

        // Prefer native mempool client when gateway has no balance field.
        return app(MempoolBitcoinClient::class)->fetchBalance($address, 'BTC', 'Bitcoin');
    }

    private function balanceEthereum(string $address): float
    {
        try {
            $json = $this->post('/eth/address', [
                'address' => $address,
                'network' => 'mainnet',
            ]);
            if (isset($json['balance']) && is_numeric($json['balance'])) {
                $raw = (string) $json['balance'];
                if (str_starts_with($raw, '0x') || ctype_digit($raw)) {
                    return $this->fromWei(str_starts_with($raw, '0x') ? (string) hexdec($raw) : $raw, 18);
                }

                return (float) $raw;
            }
        } catch (\Throwable) {
            // Fall through.
        }

        return $this->etherscan->fetchBalance($address, 'ETH', 'Ethereum');
    }

    private function balanceSolana(string $address): float
    {
        try {
            $json = $this->post('/sol/address', [
                'address' => $address,
                'network' => 'mainnet',
            ]);
            if (isset($json['balance']) && is_numeric($json['balance'])) {
                $val = (float) $json['balance'];

                return $val > 1000 ? $val / 1e9 : $val;
            }
            if (isset($json['lamports']) && is_numeric($json['lamports'])) {
                return ((int) $json['lamports']) / 1e9;
            }
        } catch (\Throwable) {
            // Fall through.
        }

        return $this->solana->fetchBalance($address, 'SOL', 'Solana');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchBitcoin(string $address, string $coin, string $networkLabel): array
    {
        $json = $this->post('/btc/address/transactions', ['address' => $address]);
        $txs = $json['transactions'] ?? [];
        if (! is_array($txs)) {
            return [];
        }

        $tip = $this->tipHeight('Bitcoin');
        $out = [];

        foreach ($txs as $tx) {
            if (! is_array($tx)) {
                continue;
            }
            $txid = (string) ($tx['txId'] ?? $tx['txid'] ?? $tx['hash'] ?? '');
            if ($txid === '') {
                continue;
            }

            $amountSats = 0;
            $outputs = $tx['outputs'] ?? $tx['vout'] ?? [];
            if (is_array($outputs)) {
                foreach ($outputs as $vout) {
                    if (! is_array($vout)) {
                        continue;
                    }
                    $addr = (string) ($vout['address'] ?? $vout['scriptpubkey_address'] ?? '');
                    if ($addr !== '' && strcasecmp($addr, $address) === 0) {
                        $amountSats += (int) ($vout['value'] ?? 0);
                    }
                }
            }

            // Some gateway payloads expose a pre-aggregated received field.
            if ($amountSats <= 0 && isset($tx['result']) && is_numeric($tx['result'])) {
                $result = (int) $tx['result'];
                if ($result > 0) {
                    $amountSats = $result;
                }
            }

            if ($amountSats <= 0) {
                continue;
            }

            $blockHeight = isset($tx['blockHeight']) ? (int) $tx['blockHeight']
                : (isset($tx['block_height']) ? (int) $tx['block_height'] : null);
            $confirmations = (int) ($tx['confirmations'] ?? 0);
            if ($confirmations <= 0 && $blockHeight && $tip) {
                $confirmations = max(0, $tip - $blockHeight + 1);
            }

            $from = null;
            foreach ($tx['inputs'] ?? [] as $vin) {
                if (! is_array($vin)) {
                    continue;
                }
                $prev = $vin['address'] ?? $vin['prevout']['address'] ?? null;
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
                'coin' => $coin,
                'network' => $networkLabel,
                'token_contract' => null,
                'raw' => $tx,
            ];
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchEthereum(string $address, string $coin, string $networkLabel): array
    {
        // Gateway has native ETH transfers only; ERC-20 needs Etherscan.
        if ($coin !== 'ETH') {
            return $this->etherscan->fetchIncoming($address, $coin, $networkLabel);
        }

        $json = $this->post('/eth/address', [
            'address' => $address,
            'network' => 'mainnet',
        ]);
        $txs = $json['transactions'] ?? [];
        if (! is_array($txs)) {
            return [];
        }

        $tip = $this->tipHeight('Ethereum');
        $out = [];

        foreach ($txs as $tx) {
            if (! is_array($tx)) {
                continue;
            }
            $to = strtolower((string) ($tx['to'] ?? ''));
            if ($to !== strtolower($address)) {
                continue;
            }
            $hash = (string) ($tx['hash'] ?? $tx['txId'] ?? '');
            if ($hash === '') {
                continue;
            }

            $rawValue = (string) ($tx['value'] ?? '0');
            $amount = $this->fromWei($rawValue, 18);
            if ($amount <= 0) {
                continue;
            }

            $blockHeight = isset($tx['blockNumber']) ? (int) $tx['blockNumber']
                : (isset($tx['blockHeight']) ? (int) $tx['blockHeight'] : null);
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
                'network' => $networkLabel,
                'token_contract' => null,
                'raw' => $tx,
            ];
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchSolana(string $address, string $coin, string $networkLabel): array
    {
        // Gateway lacks a reliable SPL transfer feed; use Solana RPC for SPL / full parse.
        if ($coin !== 'SOL') {
            return $this->solana->fetchIncoming($address, $coin, $networkLabel);
        }

        // Prefer Solana RPC for amount accuracy; gateway used for health/tip.
        // Still try gateway first for native SOL activity ids, then enrich via RPC if sparse.
        $json = $this->post('/sol/address', [
            'address' => $address,
            'network' => 'mainnet',
        ]);
        $txs = $json['transactions'] ?? [];
        if (! is_array($txs) || $txs === []) {
            return $this->solana->fetchIncoming($address, $coin, $networkLabel);
        }

        $tip = $this->tipHeight('Solana');
        $out = [];

        foreach ($txs as $tx) {
            if (! is_array($tx)) {
                continue;
            }
            $sig = (string) ($tx['txId'] ?? $tx['signature'] ?? $tx['hash'] ?? '');
            if ($sig === '') {
                continue;
            }

            $amount = 0.0;
            if (isset($tx['amount']) && is_numeric($tx['amount'])) {
                $amount = (float) $tx['amount'];
            } elseif (isset($tx['lamports']) && is_numeric($tx['lamports'])) {
                $amount = ((int) $tx['lamports']) / 1e9;
            } elseif (isset($tx['value']) && is_numeric($tx['value'])) {
                $val = (float) $tx['value'];
                $amount = $val > 1000 ? $val / 1e9 : $val;
            }

            if ($amount <= 0) {
                continue;
            }

            $slot = isset($tx['slot']) ? (int) $tx['slot']
                : (isset($tx['blockHeight']) ? (int) $tx['blockHeight'] : null);
            $confirmations = 0;
            if ($slot && $tip) {
                $confirmations = max(0, $tip - $slot + 1);
            }

            $out[] = [
                'tx_hash' => $sig,
                'amount' => $amount,
                'block_height' => $slot,
                'confirmations' => $confirmations,
                'from_address' => $tx['from'] ?? null,
                'to_address' => $address,
                'coin' => $coin,
                'network' => $networkLabel,
                'token_contract' => null,
                'raw' => $tx,
            ];
        }

        return $out !== [] ? $out : $this->solana->fetchIncoming($address, $coin, $networkLabel);
    }

    private function tipForPath(string $path, array $body): ?int
    {
        try {
            $json = $this->post($path, $body);
        } catch (\Throwable) {
            return null;
        }

        foreach (['currentHeight', 'height', 'blockHeight', 'slot'] as $key) {
            if (isset($json[$key]) && is_numeric($json[$key])) {
                return (int) $json[$key];
            }
        }

        return null;
    }

    private function probeAddress(string $chain): bool
    {
        try {
            if ($chain === 'btc') {
                $this->post('/btc/blocks', []);

                return true;
            }
        } catch (\Throwable) {
            return false;
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function post(string $path, array $body): array
    {
        $apiKey = (string) ($this->http->monitoringProvider()->credential('blockchain_com_api_key') ?? '');
        if ($apiKey === '') {
            throw new RuntimeException('Blockchain.com Explorer API key missing in Admin Settings.');
        }

        $base = rtrim((string) (
            $this->http->monitoringProvider()->credential('blockchain_com_base_url')
            ?: config('crypto.blockchain_com.base_url')
        ), '/');
        $header = (string) config('crypto.blockchain_com.auth_header', 'X-Explorer-Auth-Key');

        $res = $this->http->postJson(
            $base.$path,
            $body,
            [$header => $apiKey],
            $this->http->maxRetries()
        );

        if (! $res['ok']) {
            $status = (int) ($res['status'] ?? 0);
            if ($status === 401 || $status === 403) {
                throw new RuntimeException('Blockchain.com authentication failed (check Explorer API key).');
            }
            throw new RuntimeException($res['error'] ?? 'Blockchain.com explorer request failed');
        }

        return is_array($res['json']) ? $res['json'] : [];
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
