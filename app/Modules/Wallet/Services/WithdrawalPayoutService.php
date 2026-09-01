<?php

namespace App\Modules\Wallet\Services;

use App\Events\WithdrawalAwaitingProviderAuthorization;
use App\Models\PaymentTimelineEvent;
use App\Models\Withdrawal;
use App\Modules\Wallet\Payments\Monnify\MonnifyApiException;
use App\Modules\Wallet\Payments\Monnify\MonnifyDisbursementMapper;
use App\Modules\Wallet\Payments\PayoutGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WithdrawalPayoutService
{
    private const MAX_PROVIDER_AUTH_ATTEMPTS = 5;

    public function __construct(
        private PayoutGateway $gateway,
        private WalletService $wallets,
    ) {}

    public function merchantBalance(): float
    {
        return $this->gateway->rail()->getMerchantWalletBalance();
    }

    /**
     * Approve & Send: audit fields, initiate provider transfer, mark processing or awaiting auth.
     * Falls back to ledger-only complete when provider is not configured (manual mode).
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

        $rail = $this->gateway->rail();

        if ($rail->isConfigured()) {
            $payoutReference = null;
            $transferPayload = null;

            DB::transaction(function () use ($withdrawal, $adminId, $ip, $note, &$payoutReference, &$transferPayload) {
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

                $payoutReference = $withdrawal->provider_payout_reference
                    ?: ('WPO-'.strtoupper((string) Str::ulid()));

                $withdrawal->update([
                    'provider_payout_reference' => $payoutReference,
                    'provider' => 'monnify',
                ]);

                $transferPayload = [
                    'amount' => $withdrawal->amount,
                    'reference' => $payoutReference,
                    'bankCode' => $withdrawal->bank_code,
                    'accountNumber' => $withdrawal->account_number,
                    'accountName' => $withdrawal->account_name,
                    'narration' => 'Withdrawal '.$withdrawal->reference,
                    'async' => true,
                ];
            });

            $withdrawal->refresh();

            try {
                $result = $rail->initiateTransfer($transferPayload ?? []);
            } catch (MonnifyApiException $e) {
                if (! $this->handleInitiateError($withdrawal, $e)) {
                    throw $e;
                }

                return;
            }

            $this->applyTransferInitiateResult($withdrawal, $result);

            return;
        }

        $this->wallets->debitForWithdrawal($withdrawal, $adminId);
        $withdrawal->refresh();
        if ($ip || $note) {
            $withdrawal->update(array_filter([
                'approved_ip' => $ip,
                'approval_note' => $note,
            ]));
        }
    }

    public function authorizeProviderTransfer(Withdrawal $withdrawal, string $otp, int $adminId): void
    {
        $withdrawal = $this->refreshProviderStatus($withdrawal);

        if ($withdrawal->isProviderAuthorizationExpired()) {
            throw new InvalidArgumentException(__('This payout authorization has expired. Use Retry payout to start a new transfer.'));
        }

        if (! $withdrawal->needsProviderAuthorization()) {
            throw new InvalidArgumentException(__('This withdrawal does not require provider authorization.'));
        }

        if ((int) $withdrawal->provider_auth_attempts >= self::MAX_PROVIDER_AUTH_ATTEMPTS) {
            throw new InvalidArgumentException(__('Too many authorization attempts. Use Retry payout to start again.'));
        }

        $reference = (string) $withdrawal->provider_payout_reference;
        if ($reference === '') {
            throw new InvalidArgumentException(__('Missing provider payout reference.'));
        }

        $withdrawal->increment('provider_auth_attempts');

        try {
            $validateResult = $this->gateway->authorizeTransferIfSupported($reference, $otp);
        } catch (MonnifyApiException $e) {
            $this->refreshProviderSummary($withdrawal);
            $withdrawal->refresh();

            if ($withdrawal->isProviderAuthorizationExpired()) {
                $this->wallets->failWithdrawalPayout($withdrawal, 'Monnify authorization expired');
            }

            throw new InvalidArgumentException($e->getMessage());
        }

        $meta = $withdrawal->provider_meta ?? [];
        $meta['last_validate_otp'] = MonnifyDisbursementMapper::snapshot($validateResult);
        $withdrawal->update(['provider_meta' => $meta]);

        PaymentTimelineEvent::record($withdrawal, 'provider_authorized', 'Provider OTP submitted', [
            'admin_id' => $adminId,
            'status' => MonnifyDisbursementMapper::status($validateResult),
        ]);

        $this->finalizeFromProviderStatus($withdrawal, MonnifyDisbursementMapper::status($validateResult));
    }

    public function retryPayout(Withdrawal $withdrawal, int $adminId, ?string $ip = null): void
    {
        if (! in_array($withdrawal->internal_status, ['failed'], true)
            && ! in_array($withdrawal->status, ['failed'], true)) {
            throw new InvalidArgumentException('Only failed withdrawals can be retried.');
        }

        $withdrawal->update([
            'status' => 'pending',
            'internal_status' => 'pending_review',
            'provider_status' => null,
            'provider_payout_reference' => null,
            'provider' => null,
            'provider_meta' => null,
            'provider_auth_attempts' => 0,
        ]);

        $this->wallets->lockForWithdrawal($withdrawal->fresh());
        PaymentTimelineEvent::record($withdrawal, 'retry', 'Retry payout requested');

        $this->approveAndSend($withdrawal->fresh(), $adminId, $ip, 'Retry payout');
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function applyTransferInitiateResult(Withdrawal $withdrawal, array $result): void
    {
        $status = MonnifyDisbursementMapper::status($result);
        $meta = $withdrawal->provider_meta ?? [];
        $meta['initiate'] = MonnifyDisbursementMapper::snapshot($result);
        $withdrawal->update(['provider_meta' => $meta]);

        if ($this->gateway->requiresAuthorization($result)) {
            $this->wallets->markWithdrawalAwaitingProviderAuthorization(
                $withdrawal->fresh(),
                $status ?: 'PENDING_AUTHORIZATION',
                $meta,
                'monnify',
            );

            $fresh = $withdrawal->fresh();
            if ($fresh) {
                $this->dispatchAwaitingProviderAuthorizationEvent($fresh);
            }

            return;
        }

        $this->wallets->markWithdrawalProcessing($withdrawal->fresh(), $status ?: 'PENDING');

        if (MonnifyDisbursementMapper::isSuccess($status)) {
            $this->wallets->completeWithdrawalPayout($withdrawal->fresh());
        }
    }

    private function finalizeFromProviderStatus(Withdrawal $withdrawal, string $status): void
    {
        if (MonnifyDisbursementMapper::isSuccess($status)) {
            $this->wallets->markWithdrawalProcessing($withdrawal, $status);
            $this->wallets->completeWithdrawalPayout($withdrawal->fresh());

            return;
        }

        if (MonnifyDisbursementMapper::isTerminalFailure($status)) {
            $this->wallets->failWithdrawalPayout($withdrawal, 'Monnify disbursement '.$status);

            return;
        }

        $this->refreshProviderSummary($withdrawal);
        $summaryStatus = strtoupper((string) $withdrawal->fresh()->provider_status);

        if (MonnifyDisbursementMapper::isSuccess($summaryStatus)) {
            $this->wallets->markWithdrawalProcessing($withdrawal->fresh(), $summaryStatus);
            $this->wallets->completeWithdrawalPayout($withdrawal->fresh());

            return;
        }

        if (MonnifyDisbursementMapper::isInFlight($summaryStatus)) {
            $this->wallets->markWithdrawalProcessing($withdrawal->fresh(), $summaryStatus);
        }
    }

    private function refreshProviderSummary(Withdrawal $withdrawal): void
    {
        $reference = (string) $withdrawal->provider_payout_reference;
        if ($reference === '' || ! $this->gateway->rail()->isConfigured()) {
            return;
        }

        try {
            $summary = $this->gateway->rail()->getTransferStatus($reference);
            $st = MonnifyDisbursementMapper::status($summary);
            $meta = $withdrawal->provider_meta ?? [];
            $meta['last_summary'] = MonnifyDisbursementMapper::snapshot($summary);
            $withdrawal->update([
                'provider_status' => $st ?: $withdrawal->provider_status,
                'provider_meta' => $meta,
            ]);
        } catch (\Throwable) {
            // Keep existing status when requery fails.
        }
    }

    /**
     * @return bool true when the withdrawal was recovered or failed cleanly (caller should not rethrow)
     */
    private function handleInitiateError(Withdrawal $withdrawal, MonnifyApiException $e): bool
    {
        $code = $e->errorCode;

        if ($code === 'D05' && $withdrawal->provider_payout_reference) {
            try {
                $status = $this->gateway->rail()->getTransferStatus($withdrawal->provider_payout_reference);
                $st = MonnifyDisbursementMapper::status($status);
                if (MonnifyDisbursementMapper::isSuccess($st)) {
                    $this->wallets->markWithdrawalProcessing($withdrawal, $st);
                    $this->wallets->completeWithdrawalPayout($withdrawal->fresh());

                    return true;
                }
                if ($st === 'PENDING_AUTHORIZATION') {
                    $meta = $withdrawal->provider_meta ?? [];
                    $meta['initiate'] = MonnifyDisbursementMapper::snapshot($status);
                    $meta['last_summary'] = MonnifyDisbursementMapper::snapshot($status);
                    $this->wallets->markWithdrawalAwaitingProviderAuthorization($withdrawal, $st, $meta, 'monnify');
                    $this->dispatchAwaitingProviderAuthorizationEvent($withdrawal->fresh());

                    return true;
                }
                if (MonnifyDisbursementMapper::isInFlight($st)) {
                    $this->wallets->markWithdrawalProcessing($withdrawal, $st);

                    return true;
                }
            } catch (\Throwable) {
                // Fall through to fail + unlock.
            }
        }

        $this->wallets->failWithdrawalPayout(
            $withdrawal->fresh(),
            'Monnify initiate failed ['.$code.']: '.mb_substr($e->getMessage(), 0, 400)
        );

        return false;
    }

    /**
     * Re-query Monnify and update provider_status / provider_meta (e.g. before admin detail view).
     */
    public function refreshProviderStatus(Withdrawal $withdrawal): Withdrawal
    {
        $this->refreshProviderSummary($withdrawal);
        $withdrawal = $withdrawal->fresh() ?? $withdrawal;

        if ($withdrawal->isProviderAuthorizationExpired() && ! $withdrawal->isTerminal()) {
            $this->wallets->failWithdrawalPayout($withdrawal, 'Monnify authorization expired');

            return $withdrawal->fresh() ?? $withdrawal;
        }

        return $withdrawal;
    }

    private function dispatchAwaitingProviderAuthorizationEvent(Withdrawal $withdrawal): void
    {
        WithdrawalAwaitingProviderAuthorization::dispatch(
            (int) $withdrawal->id,
            (int) $withdrawal->user_id,
            (float) $withdrawal->amount,
            (string) $withdrawal->provider_payout_reference,
            (string) ($withdrawal->currency ?: 'NGN'),
        );
    }
}
