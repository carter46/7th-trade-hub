<?php

namespace App\Modules\Wallet\Services;

use App\Enums\TransactionType;
use App\Enums\WalletType;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletFunding;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WalletService
{
    public function creditFromFunding(
        WalletFunding $funding,
        ?int $approvedBy = null,
        ?string $approvedIp = null,
        ?string $approvedDevice = null,
        ?string $approvedReason = null,
    ): Transaction {
        return DB::transaction(function () use ($funding, $approvedBy, $approvedIp, $approvedDevice, $approvedReason) {
            $funding = WalletFunding::where('id', $funding->id)->lockForUpdate()->firstOrFail();

            if ($funding->status === 'approved') {
                return $this->findFundingTransaction($funding)
                    ?? throw new InvalidArgumentException('Approved funding has no ledger entry.');
            }

            if ($funding->status !== 'pending') {
                throw new InvalidArgumentException('Funding is not pending.');
            }

            $wallet = Wallet::where('id', $funding->wallet_id)->lockForUpdate()->firstOrFail();

            $wallet->balance = bcadd((string) $wallet->balance, (string) $funding->amount, 2);
            $wallet->save();

            $transaction = $this->createLedgerEntry($wallet, [
                'user_id' => $funding->user_id,
                'wallet_funding_id' => $funding->id,
                'type' => TransactionType::Funding->value,
                'label' => 'Deposit ('.$funding->method.')',
                'description' => 'Wallet funding approved',
                'amount' => $funding->amount,
                'currency' => $funding->currency,
                'status' => 'completed',
            ]);

            $funding->update(array_filter([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => $approvedBy ? now() : null,
                'approved_ip' => $approvedIp,
                'approved_device' => $approvedDevice,
                'approved_reason' => $approvedReason,
            ], fn ($v) => $v !== null));

            return $transaction;
        });
    }

    public function debitForPurchase(Wallet $wallet, Order $order, float $amount, ?int $escrowId = null): Transaction
    {
        return DB::transaction(function () use ($wallet, $order, $amount, $escrowId) {
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();

            if (bccomp((string) $wallet->balance, (string) $amount, 2) < 0) {
                throw new InvalidArgumentException('Insufficient wallet balance.');
            }

            $wallet->balance = bcsub((string) $wallet->balance, (string) $amount, 2);
            $wallet->locked_balance = bcadd((string) $wallet->locked_balance, (string) $amount, 2);
            $wallet->save();

            return $this->createLedgerEntry($wallet, [
                'user_id' => $wallet->user_id,
                'order_id' => $order->id,
                'escrow_id' => $escrowId,
                'type' => TransactionType::EscrowLock->value,
                'label' => 'Purchase escrow',
                'description' => 'Funds locked for order '.$order->reference,
                'amount' => -$amount,
                'currency' => 'NGN',
                'status' => 'completed',
            ]);
        });
    }

    public function releaseEscrow(Escrow $escrow, ?int $releasedBy = null, float $feePercent = 0): void
    {
        DB::transaction(function () use ($escrow, $releasedBy, $feePercent) {
            $escrow = Escrow::where('id', $escrow->id)->lockForUpdate()->firstOrFail();

            if ($escrow->status === 'released') {
                return;
            }

            if ($escrow->status !== 'locked') {
                throw new InvalidArgumentException('Escrow is not locked.');
            }

            $buyerWallet = Wallet::where('id', $escrow->buyer_wallet_id)->lockForUpdate()->firstOrFail();
            $buyerWallet->locked_balance = bcsub((string) $buyerWallet->locked_balance, (string) $escrow->amount, 2);
            $buyerWallet->save();

            $fee = bcmul((string) $escrow->amount, (string) ($feePercent / 100), 2);
            $sellerAmount = bcsub((string) $escrow->amount, $fee, 2);

            if ($escrow->seller_wallet_id) {
                $sellerWallet = Wallet::where('id', $escrow->seller_wallet_id)->lockForUpdate()->firstOrFail();
                $sellerWallet->balance = bcadd((string) $sellerWallet->balance, $sellerAmount, 2);
                $sellerWallet->save();

                $this->createLedgerEntry($sellerWallet, [
                    'user_id' => $sellerWallet->user_id,
                    'order_id' => $escrow->order_id,
                    'escrow_id' => $escrow->id,
                    'type' => TransactionType::EscrowRelease->value,
                    'label' => 'Escrow released',
                    'amount' => $sellerAmount,
                    'currency' => 'NGN',
                    'status' => 'completed',
                ]);
            }

            if (bccomp($fee, '0', 2) > 0) {
                $platformWallet = $this->getPlatformWallet();
                $platformWallet = Wallet::where('id', $platformWallet->id)->lockForUpdate()->firstOrFail();
                $platformWallet->balance = bcadd((string) $platformWallet->balance, $fee, 2);
                $platformWallet->save();

                $this->createLedgerEntry($platformWallet, [
                    'user_id' => $platformWallet->user_id,
                    'order_id' => $escrow->order_id,
                    'escrow_id' => $escrow->id,
                    'type' => TransactionType::PlatformFee->value,
                    'label' => 'Platform fee',
                    'amount' => $fee,
                    'currency' => 'NGN',
                    'status' => 'completed',
                ]);
            }

            $escrow->update([
                'status' => 'released',
                'released_at' => now(),
                'released_by' => $releasedBy,
            ]);
        });
    }

    public function refundEscrow(Escrow $escrow, ?float $refundAmount = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($escrow, $refundAmount, $reason) {
            $escrow = Escrow::where('id', $escrow->id)->lockForUpdate()->firstOrFail();

            if (in_array($escrow->status, ['refunded', 'partial_refund'], true)) {
                return;
            }

            if ($escrow->status !== 'locked') {
                throw new InvalidArgumentException('Escrow is not locked.');
            }

            $amount = $refundAmount ?? (float) $escrow->amount;
            $buyerWallet = Wallet::where('id', $escrow->buyer_wallet_id)->lockForUpdate()->firstOrFail();

            $buyerWallet->locked_balance = bcsub((string) $buyerWallet->locked_balance, (string) $amount, 2);
            $buyerWallet->balance = bcadd((string) $buyerWallet->balance, (string) $amount, 2);
            $buyerWallet->save();

            $this->createLedgerEntry($buyerWallet, [
                'user_id' => $buyerWallet->user_id,
                'order_id' => $escrow->order_id,
                'escrow_id' => $escrow->id,
                'type' => TransactionType::Refund->value,
                'label' => 'Escrow refund',
                'description' => $reason,
                'amount' => $amount,
                'currency' => 'NGN',
                'status' => 'completed',
            ]);

            $escrow->update([
                'status' => $refundAmount && $refundAmount < (float) $escrow->amount ? 'partial_refund' : 'refunded',
                'refunded_at' => now(),
                'refund_amount' => $amount,
                'reason' => $reason,
            ]);
        });
    }

    public function lockForWithdrawal(Withdrawal $withdrawal): void
    {
        DB::transaction(function () use ($withdrawal) {
            $wallet = Wallet::where('id', $withdrawal->wallet_id)->lockForUpdate()->firstOrFail();

            if (bccomp((string) $wallet->balance, (string) $withdrawal->amount, 2) < 0) {
                throw new InvalidArgumentException('Insufficient balance for withdrawal.');
            }

            $wallet->balance = bcsub((string) $wallet->balance, (string) $withdrawal->amount, 2);
            $wallet->locked_balance = bcadd((string) $wallet->locked_balance, (string) $withdrawal->amount, 2);
            $wallet->save();
        });
    }

    public function debitForWithdrawal(Withdrawal $withdrawal, ?int $approvedBy = null): Transaction
    {
        return DB::transaction(function () use ($withdrawal, $approvedBy) {
            $withdrawal = Withdrawal::where('id', $withdrawal->id)->lockForUpdate()->firstOrFail();

            if ($withdrawal->status === 'completed') {
                return $this->findWithdrawalTransaction($withdrawal)
                    ?? throw new InvalidArgumentException('Completed withdrawal has no ledger entry.');
            }

            if ($withdrawal->status !== 'pending') {
                throw new InvalidArgumentException('Withdrawal is not pending.');
            }

            $wallet = Wallet::where('id', $withdrawal->wallet_id)->lockForUpdate()->firstOrFail();

            if (bccomp((string) $wallet->locked_balance, (string) $withdrawal->amount, 2) < 0) {
                throw new InvalidArgumentException('Insufficient locked balance for withdrawal.');
            }

            $wallet->locked_balance = bcsub((string) $wallet->locked_balance, (string) $withdrawal->amount, 2);
            $wallet->save();

            $transaction = $this->createLedgerEntry($wallet, [
                'user_id' => $withdrawal->user_id,
                'withdrawal_id' => $withdrawal->id,
                'type' => TransactionType::Withdrawal->value,
                'label' => 'Withdrawal to bank',
                'amount' => -$withdrawal->amount,
                'currency' => 'NGN',
                'status' => 'completed',
            ]);

            $withdrawal->update(array_filter([
                'status' => 'completed',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ], fn ($v) => $v !== null));

            return $transaction;
        });
    }

    public function unlockRejectedWithdrawal(Withdrawal $withdrawal, ?string $adminNotes = null): void
    {
        DB::transaction(function () use ($withdrawal, $adminNotes) {
            $withdrawal = Withdrawal::where('id', $withdrawal->id)->lockForUpdate()->firstOrFail();

            if ($withdrawal->status === 'rejected') {
                return;
            }

            if ($withdrawal->status !== 'pending') {
                throw new InvalidArgumentException('Withdrawal is not pending.');
            }

            $wallet = Wallet::where('id', $withdrawal->wallet_id)->lockForUpdate()->firstOrFail();

            if (bccomp((string) $wallet->locked_balance, (string) $withdrawal->amount, 2) < 0) {
                throw new InvalidArgumentException('Insufficient locked balance to unlock.');
            }

            $wallet->locked_balance = bcsub((string) $wallet->locked_balance, (string) $withdrawal->amount, 2);
            $wallet->balance = bcadd((string) $wallet->balance, (string) $withdrawal->amount, 2);
            $wallet->save();

            $this->createLedgerEntry($wallet, [
                'user_id' => $withdrawal->user_id,
                'withdrawal_id' => $withdrawal->id,
                'type' => TransactionType::WithdrawalUnlock->value,
                'label' => 'Withdrawal rejected — funds returned',
                'amount' => $withdrawal->amount,
                'currency' => 'NGN',
                'status' => 'completed',
            ]);

            $withdrawal->update([
                'status' => 'rejected',
                'admin_notes' => $adminNotes,
            ]);
        });
    }

    public function adminAdjust(Wallet $wallet, float $amount, string $reason, int $adminId): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount, $reason, $adminId) {
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();

            $newBalance = bcadd((string) $wallet->balance, (string) $amount, 2);
            if (bccomp($newBalance, '0', 2) < 0) {
                throw new InvalidArgumentException('Adjustment would result in negative balance.');
            }

            $wallet->balance = $newBalance;
            $wallet->save();

            return $this->createLedgerEntry($wallet, [
                'user_id' => $wallet->user_id,
                'type' => TransactionType::AdminAdjustment->value,
                'label' => 'Admin adjustment',
                'description' => $reason.' (admin #'.$adminId.')',
                'amount' => $amount,
                'currency' => 'NGN',
                'status' => 'completed',
            ]);
        });
    }

    public function reverseTransaction(Transaction $original, string $reason, ?int $adminId = null): Transaction
    {
        if ($original->reverses_transaction_id) {
            throw new InvalidArgumentException('Cannot reverse a reversal entry.');
        }

        if ($original->type !== TransactionType::Funding->value) {
            throw new InvalidArgumentException('Only funding transactions can be reversed.');
        }

        $existingReversal = Transaction::query()
            ->where('reverses_transaction_id', $original->id)
            ->exists();

        if ($existingReversal) {
            throw new InvalidArgumentException('This transaction has already been reversed.');
        }

        return DB::transaction(function () use ($original, $reason, $adminId) {
            $wallet = Wallet::where('id', $original->wallet_id)->lockForUpdate()->firstOrFail();
            $reverseAmount = bcmul((string) $original->amount, '-1', 2);
            $newBalance = bcadd((string) $wallet->balance, $reverseAmount, 2);

            if (bccomp($newBalance, '0', 2) < 0) {
                throw new InvalidArgumentException('Reversal would result in negative balance.');
            }

            $wallet->balance = $newBalance;
            $wallet->save();

            $reversal = $this->createLedgerEntry($wallet, [
                'user_id' => $original->user_id,
                'wallet_funding_id' => $original->wallet_funding_id,
                'order_id' => $original->order_id,
                'withdrawal_id' => $original->withdrawal_id,
                'escrow_id' => $original->escrow_id,
                'reverses_transaction_id' => $original->id,
                'type' => TransactionType::Reversal->value,
                'label' => 'Reversal',
                'description' => $reason.($adminId ? ' (admin #'.$adminId.')' : ''),
                'amount' => $reverseAmount,
                'currency' => $original->currency,
                'status' => 'completed',
            ]);

            if ($original->wallet_funding_id) {
                WalletFunding::where('id', $original->wallet_funding_id)->update([
                    'status' => 'reversed',
                    'reversed_at' => now(),
                    'reversal_transaction_id' => $reversal->id,
                ]);
            }

            return $reversal;
        });
    }

    public function getPlatformWallet(): Wallet
    {
        $wallet = Wallet::query()
            ->where('type', WalletType::Platform->value)
            ->first();

        if ($wallet) {
            return $wallet;
        }

        $systemUser = \App\Models\User::firstOrCreate(
            ['email' => 'platform-wallet@internal.7thtradehub'],
            [
                'name' => 'Platform Wallet',
                'username' => 'platform_wallet',
                'password' => bcrypt(Str::random(64)),
                'email_verified_at' => now(),
            ]
        );

        return Wallet::create([
            'user_id' => $systemUser->id,
            'type' => WalletType::Platform->value,
            'balance' => 0,
            'locked_balance' => 0,
            'currency' => 'NGN',
            'gateway_subaccount_id' => 'platform',
            'status' => 'active',
        ]);
    }

    public function walletSnapshot(Wallet $wallet): array
    {
        return [
            'wallet_id' => $wallet->id,
            'balance' => (string) $wallet->balance,
            'locked_balance' => (string) $wallet->locked_balance,
        ];
    }

    private function findFundingTransaction(WalletFunding $funding): ?Transaction
    {
        return Transaction::query()
            ->where('wallet_funding_id', $funding->id)
            ->where('type', TransactionType::Funding->value)
            ->first();
    }

    private function findWithdrawalTransaction(Withdrawal $withdrawal): ?Transaction
    {
        return Transaction::query()
            ->where('withdrawal_id', $withdrawal->id)
            ->where('type', TransactionType::Withdrawal->value)
            ->first();
    }

    private function createLedgerEntry(Wallet $wallet, array $data): Transaction
    {
        return Transaction::create(array_merge([
            'wallet_id' => $wallet->id,
            'reference' => 'TXN-'.strtoupper(Str::random(10)),
        ], $data));
    }
}
