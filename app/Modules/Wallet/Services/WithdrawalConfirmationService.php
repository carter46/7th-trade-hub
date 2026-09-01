<?php

namespace App\Modules\Wallet\Services;

use App\Events\WithdrawalRequested;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\Communications\Email\EmailProfile;
use App\Services\Communications\Email\EmailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WithdrawalConfirmationService
{
    public function __construct(
        private SecurityVerificationService $security,
        private WalletService $wallets,
        private EmailService $email,
    ) {}

    public function assertCanRequest(User $user): void
    {
        if (! $user->hasPasswordSet()) {
            throw ValidationException::withMessages([
                'password' => __('Set a password on your account before requesting a withdrawal.'),
            ]);
        }

        if (! $user->activeBankAccount) {
            throw ValidationException::withMessages([
                'bank' => __('Add and verify a withdrawal bank before requesting a payout.'),
            ]);
        }

        if (SystemSetting::kycRequired()) {
            $minLevel = SystemSetting::kycRequiredLevel('withdrawal', 1);
            if (! $user->hasApprovedKyc($minLevel)) {
                throw ValidationException::withMessages([
                    'amount' => __('Complete KYC verification (level :level) before withdrawing.', [
                        'level' => $minLevel,
                    ]),
                ]);
            }
        }

        $hasOpen = Withdrawal::where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereIn('status', Withdrawal::OPEN_STATUSES)
                    ->orWhereIn('internal_status', Withdrawal::OPEN_INTERNAL);
            })
            ->exists();

        if ($hasOpen) {
            throw ValidationException::withMessages([
                'amount' => __('You already have a withdrawal in progress.'),
            ]);
        }
    }

    public function sendOtp(User $user, string $password, float $amount, int $bankId): void
    {
        $this->assertCanRequest($user);

        $bank = $user->activeBankAccount;
        if (! $bank || (int) $bank->id !== $bankId) {
            throw ValidationException::withMessages([
                'user_bank_account_id' => __('Select your verified withdrawal bank.'),
            ]);
        }

        $withdrawalMin = (float) SystemSetting::get('withdrawal_min_amount', 100);
        $withdrawalMax = (float) SystemSetting::get('withdrawal_max_amount', 1000000);

        if ($amount < $withdrawalMin || $amount > $withdrawalMax) {
            throw ValidationException::withMessages([
                'amount' => __('Amount must be between :min and :max.', [
                    'min' => number_format($withdrawalMin, 2),
                    'max' => number_format($withdrawalMax, 2),
                ]),
            ]);
        }

        $wallet = $user->wallet;
        if (! $wallet || $wallet->availableBalance() < $amount) {
            throw ValidationException::withMessages([
                'amount' => __('Insufficient available balance.'),
            ]);
        }

        $code = $this->security->start(
            $user,
            SecurityVerificationService::PURPOSE_WITHDRAWAL_REQUEST,
            $password,
            [
                'amount' => $amount,
                'user_bank_account_id' => $bankId,
            ],
        );

        $html = View::make('emails.withdrawal-request-otp', ['code' => $code, 'user' => $user])->render();
        $this->email->sendMailableHtml(
            to: $user->email,
            subject: 'Withdrawal request verification code',
            html: $html,
            profile: EmailProfile::NoReply,
            templateKey: 'withdrawal_request_otp',
        );
    }

    public function verifyOtpAndCreate(User $user, string $otp): Withdrawal
    {
        $this->security->verify($user, SecurityVerificationService::PURPOSE_WITHDRAWAL_REQUEST, $otp);
        $payload = $this->security->consumeVerifiedPayload($user, SecurityVerificationService::PURPOSE_WITHDRAWAL_REQUEST);

        $amount = (float) ($payload['amount'] ?? 0);
        $bankId = (int) ($payload['user_bank_account_id'] ?? 0);
        $bank = $user->activeBankAccount;

        if (! $bank || (int) $bank->id !== $bankId || $amount <= 0) {
            throw ValidationException::withMessages([
                'otp' => __('Your verification session expired. Please start again.'),
            ]);
        }

        $this->assertCanRequest($user);

        $wallet = $user->wallet;
        if (! $wallet) {
            throw ValidationException::withMessages([
                'amount' => __('Create a wallet first.'),
            ]);
        }

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'user_bank_account_id' => $bank->id,
            'amount' => $amount,
            'currency' => 'NGN',
            'bank_name' => $bank->bank_name,
            'bank_code' => $bank->bank_code,
            'account_number' => $bank->account_number,
            'account_name' => $bank->verified_name,
            'status' => 'pending',
            'internal_status' => 'pending_review',
            'reference' => 'WDR-'.strtoupper(Str::random(10)),
        ]);

        try {
            $this->wallets->lockForWithdrawal($withdrawal);
        } catch (\InvalidArgumentException $e) {
            $withdrawal->delete();
            throw ValidationException::withMessages(['amount' => $e->getMessage()]);
        }

        DB::afterCommit(function () use ($withdrawal) {
            WithdrawalRequested::dispatch(
                (int) $withdrawal->id,
                (int) $withdrawal->user_id,
                (float) $withdrawal->amount,
                (string) $withdrawal->currency
            );
        });

        return $withdrawal;
    }
}
