<?php

namespace App\Modules\Wallet\Services\Blockchain;

use App\Models\CryptoSellRequest;
use App\Models\IncomingCryptoTransaction;
use App\Models\OtcPricingSetting;
use App\Modules\Wallet\Services\WalletAllocationService;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationMessage;
use Illuminate\Support\Facades\DB;

class DepositMatchingService
{
    public function __construct(
        private NotificationDispatcher $notifications,
        private WalletAllocationService $allocation,
    ) {}

    public function matchUnmatched(): int
    {
        $rows = IncomingCryptoTransaction::query()
            ->whereNull('matched_order_id')
            ->where('status', IncomingCryptoTransaction::STATUS_DETECTED)
            ->orderBy('detected_at')
            ->get();

        $matched = 0;
        foreach ($rows as $row) {
            if ($this->tryMatch($row)) {
                $matched++;
            }
        }

        return $matched;
    }

    public function tryMatch(IncomingCryptoTransaction $row): bool
    {
        if ($row->matched_order_id) {
            return false;
        }

        return DB::transaction(function () use ($row) {
            $row = IncomingCryptoTransaction::query()->where('id', $row->id)->lockForUpdate()->first();
            if (! $row || $row->matched_order_id) {
                return false;
            }

            $tolerance = (float) OtcPricingSetting::current()->tolerance_percent;
            $precision = $this->allocation->precisionFor($row->coin);
            $receivedKey = $this->allocation->amountKey((float) $row->amount, $precision);

            $candidates = CryptoSellRequest::query()
                ->whereIn('status', CryptoSellRequest::OPEN_STATUSES)
                ->where(function ($q) {
                    // Quote expiry only applies to waiting_deposit; submitted/verifying keep matching.
                    $q->whereNotIn('status', [
                        CryptoSellRequest::STATUS_WAITING_DEPOSIT,
                        'pending',
                    ])->orWhere(function ($q2) {
                        $q2->whereIn('status', [
                            CryptoSellRequest::STATUS_WAITING_DEPOSIT,
                            'pending',
                        ])->where(function ($q3) {
                            $q3->whereNull('expires_at')->orWhere('expires_at', '>', now());
                        });
                    });
                })
                ->whereRaw('UPPER(coin) = ?', [strtoupper($row->coin)])
                ->where('platform_address', $row->wallet_address)
                ->where(function ($q) use ($row) {
                    $q->whereNull('tx_hash')->orWhere('tx_hash', $row->tx_hash);
                })
                ->orderBy('created_at')
                ->lockForUpdate()
                ->get()
                ->filter(function (CryptoSellRequest $order) use ($row) {
                    return strcasecmp((string) $order->network, (string) $row->network) === 0
                        || $this->networksCompatible((string) $order->network, (string) $row->network);
                })
                ->values();

            if ($candidates->isEmpty()) {
                $this->notifyUnmatched($row, 'no open order for '.$row->wallet_address);

                return false;
            }

            // Primary: exact fingerprint match
            $exact = $candidates->first(function (CryptoSellRequest $order) use ($receivedKey, $precision) {
                return $this->allocation->amountKey((float) $order->amount_crypto, $precision) === $receivedKey;
            });

            if ($exact) {
                return $this->attach($row, $exact, 'exact');
            }

            // Fallback: tolerance only on this wallet; 2+ within tolerance = ambiguous / unmatched
            $within = $candidates->map(function (CryptoSellRequest $order) use ($row, $tolerance) {
                $expected = (float) $order->amount_crypto;
                $received = (float) $row->amount;
                $diffPct = $expected > 0 ? abs($received - $expected) / $expected * 100 : 100;

                return [
                    'order' => $order,
                    'diff' => $diffPct,
                    'match_status' => $received < $expected ? 'underpaid' : 'overpaid',
                    'in_tolerance' => $diffPct <= $tolerance,
                ];
            })->filter(fn ($x) => $x['in_tolerance'])->sortBy('diff')->values();

            if ($within->isEmpty()) {
                $this->notifyUnmatched($row, 'no fingerprint/tolerance match on wallet');

                return false;
            }

            if ($within->count() > 1) {
                $this->notifyUnmatched($row, 'ambiguous amount match for '.$row->tx_hash, 'ambiguous:'.$row->tx_hash);

                return false;
            }

            $best = $within->first();

            return $this->attach($row, $best['order'], $best['match_status']);
        });
    }

    private function attach(IncomingCryptoTransaction $row, CryptoSellRequest $order, string $matchStatus): bool
    {
        $row->matched_order_id = $order->id;
        $row->status = IncomingCryptoTransaction::STATUS_MATCHED;
        $row->save();

        $order->tx_hash = $row->tx_hash;
        $order->amount_match_status = $matchStatus;
        $order->confirmations_observed = (int) $row->confirmations;

        $required = (int) ($order->required_confirmations ?? 1);
        if ($matchStatus === 'underpaid') {
            $order->status = CryptoSellRequest::STATUS_UNDERPAID;
        } elseif ($matchStatus === 'overpaid') {
            $order->status = CryptoSellRequest::STATUS_OVERPAID;
        } elseif ((int) $row->confirmations >= $required) {
            $order->status = CryptoSellRequest::STATUS_VERIFYING;
            $row->status = IncomingCryptoTransaction::STATUS_READY;
            $row->save();
        } else {
            $order->status = CryptoSellRequest::STATUS_SUBMITTED;
        }
        $order->save();

        $this->notifications->notifyAdmins(new NotificationMessage(
            type: 'crypto.deposit_matched',
            title: 'Deposit Detected',
            body: sprintf(
                '%s matched to Order #%d (%s)',
                $row->coin,
                $order->id,
                $matchStatus
            ),
            actionUrl: route('admin.crypto-sells.show', $order),
            meta: [
                'severity' => 'info',
                'order_id' => $order->id,
                'tx_hash' => $row->tx_hash,
                'match_status' => $matchStatus,
            ],
            priority: 'normal',
            permission: 'finance.manage',
            dedupeKey: 'matched:'.$row->tx_hash,
        ), ['database']);

        return true;
    }

    private function notifyUnmatched(IncomingCryptoTransaction $row, string $detail, ?string $dedupe = null): void
    {
        $this->notifications->notifyAdmins(new NotificationMessage(
            type: 'crypto.deposit_unmatched',
            title: 'Unmatched Deposit',
            body: sprintf(
                '%s (%s) %.8f — %s',
                $row->coin,
                $row->network,
                (float) $row->amount,
                $detail
            ),
            actionUrl: route('admin.incoming-deposits'),
            meta: ['severity' => 'warning', 'tx_hash' => $row->tx_hash],
            priority: 'high',
            permission: 'finance.manage',
            dedupeKey: $dedupe ?? 'unmatched:'.$row->tx_hash,
        ), ['database']);
    }

    private function networksCompatible(string $a, string $b): bool
    {
        $norm = fn (string $n) => strtolower(str_replace([' ', '-'], '', $n));
        $map = [
            'bitcoin' => 'bitcoin',
            'btc' => 'bitcoin',
            'ethereum' => 'ethereum',
            'eth' => 'ethereum',
            'erc20' => 'ethereum',
            'bep20' => 'bep20',
            'bsc' => 'bep20',
            'polygon' => 'polygon',
            'matic' => 'polygon',
            'base' => 'base',
            'arbitrum' => 'arbitrum',
            'arb' => 'arbitrum',
            'trc20' => 'tron',
            'tron' => 'tron',
            'solana' => 'solana',
            'sol' => 'solana',
        ];
        $na = $map[$norm($a)] ?? $norm($a);
        $nb = $map[$norm($b)] ?? $norm($b);

        return $na === $nb;
    }
}
