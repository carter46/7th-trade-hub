<?php

namespace App\Modules\Wallet\Services;

use App\Models\PaymentTimelineEvent;
use App\Models\Withdrawal;
use App\Modules\Wallet\Payments\Contracts\PaymentRailInterface;
use App\Modules\Wallet\Payments\Monnify\MonnifyApiException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WithdrawalPayoutService
{
    public function __construct(
        private PaymentRailInterface $rail,
        private WalletService $wallets,
    ) {}

    public function merchantBalance(): float
    {
        return $this->rail->getMerchantWalletBalance();
    }

    /**
     * Approve & Send: audit fields, initiate Monnify transfer, mark processing.
     * Falls back to ledger-only complete when Monnify is not configured (manual mode).
     */
    public function approveAndSend(
        Withdrawal $withdrawal,
        int $adminId,
        ?string $ip = null,
        ?string $note = null,
    ): void {
        if (! in_array($withdrawal->status, ['pending'], true)
            && $withdrawal->internal_status !== 'pending_review') {
            throw new InvalidArgumentException('Withdrawal is not pending review.');
        }

        if ($this->rail->isConfigured()) {
            // Capture initiate errors so fail/unlock can commit before rethrowing.
            $initiateError = null;

            // Serialize sends + re-check merchant float under a short advisory lock.
            DB::transaction(function () use ($withdrawal, $adminId, $ip, $note, &$initiateError) {
                $withdrawal = Withdrawal::where('id', $withdrawal->id)->lockForUpdate()->firstOrFail();

                if (! in_array($withdrawal->status, ['pending'], true)
                    && $withdrawal->internal_status !== 'pending_review') {
                    throw new InvalidArgumentException('Withdrawal is not pending review.');
                }

                $merchantBalance = $this->merchantBalance();
                if (bccomp((string) $merchantBalance, (string) $withdrawal->amount, 2) < 0) {
                    throw new InvalidArgumentException(
                        'Insufficient merchant balance. Merchant ₦'.number_format($merchantBalance, 2)
                        .' vs withdrawal ₦'.number_format((float) $withdrawal->amount, 2).'.'
                    );
                }

                $this->wallets->markWithdrawalApproved($withdrawal, $adminId, $ip, $note);
                $withdrawal->refresh();

                $reference = $withdrawal->provider_payout_reference
                    ?: ('WPO-'.strtoupper((string) Str::ulid()));

                $withdrawal->update(['provider_payout_reference' => $reference]);

                try {
                    $result = $this->rail->initiateTransfer([
                        'amount' => $withdrawal->amount,
                        'reference' => $reference,
                        'bankCode' => $withdrawal->bank_code,
                        'accountNumber' => $withdrawal->account_number,
                        'accountName' => $withdrawal->account_name,
                        'narration' => 'Withdrawal '.$withdrawal->reference,
                        'async' => true,
                    ]);
                } catch (MonnifyApiException $e) {
                    if (! $this->handleInitiateError($withdrawal, $e)) {
                        // Failed + unlocked inside this transaction — rethrow after commit.
                        $initiateError = $e;
                    }

                    return;
                }

                $providerStatus = (string) ($result['status'] ?? 'PENDING');
                $this->wallets->markWithdrawalProcessing($withdrawal, $providerStatus);

                if (in_array(strtoupper($providerStatus), ['SUCCESS', 'COMPLETED'], true)) {
                    $this->wallets->completeWithdrawalPayout($withdrawal->fresh());
                }
            });

            if ($initiateError instanceof MonnifyApiException) {
                throw $initiateError;
            }

            return;
        }

        // Manual / Monnify off: legacy complete on approve.
        $this->wallets->debitForWithdrawal($withdrawal, $adminId);
        $withdrawal->refresh();
        if ($ip || $note) {
            $withdrawal->update(array_filter([
                'approved_ip' => $ip,
                'approval_note' => $note,
            ]));
        }
    }

    public function retryPayout(Withdrawal $withdrawal, int $adminId, ?string $ip = null): void
    {
        if (! in_array($withdrawal->internal_status, ['failed'], true)
            && ! in_array($withdrawal->status, ['failed'], true)) {
            throw new InvalidArgumentException('Only failed withdrawals can be retried.');
        }

        // Re-lock funds for a new attempt.
        $withdrawal->update([
            'status' => 'pending',
            'internal_status' => 'pending_review',
            'provider_status' => null,
            'provider_payout_reference' => null,
        ]);

        $this->wallets->lockForWithdrawal($withdrawal->fresh());
        PaymentTimelineEvent::record($withdrawal, 'retry', 'Retry payout requested');

        $this->approveAndSend($withdrawal->fresh(), $adminId, $ip, 'Retry payout');
    }

    /**
     * @return bool true when the withdrawal was recovered or failed cleanly (caller should not rethrow)
     */
    private function handleInitiateError(Withdrawal $withdrawal, MonnifyApiException $e): bool
    {
        $code = $e->errorCode;

        if ($code === 'D05' && $withdrawal->provider_payout_reference) {
            try {
                $status = $this->rail->getTransferStatus($withdrawal->provider_payout_reference);
                $st = strtoupper((string) ($status['status'] ?? ''));
                if (in_array($st, ['SUCCESS', 'COMPLETED'], true)) {
                    $this->wallets->markWithdrawalProcessing($withdrawal, $st);
                    $this->wallets->completeWithdrawalPayout($withdrawal->fresh());

                    return true;
                }
                if (in_array($st, ['PENDING', 'AWAITING_PROCESSING', 'IN_PROGRESS', 'PENDING_AUTHORIZATION'], true)) {
                    $this->wallets->markWithdrawalProcessing($withdrawal, $st);

                    return true;
                }
            } catch (\Throwable) {
                // Fall through to fail + unlock.
            }
        }

        // Non-recoverable initiate failure: unlock funds and mark failed so admin can Retry.
        $this->wallets->failWithdrawalPayout(
            $withdrawal->fresh(),
            'Monnify initiate failed ['.$code.']: '.mb_substr($e->getMessage(), 0, 400)
        );

        return false;
    }
}
