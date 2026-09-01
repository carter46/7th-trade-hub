<?php

namespace App\Modules\Wallet\Services;

use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\Withdrawal;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Wallet\Payments\Contracts\PaymentRailInterface;
use App\Services\Communications\Email\EmailProfile;
use App\Services\Communications\Email\EmailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class BankAccountService
{
    public function __construct(
        private PaymentRailInterface $rail,
        private BankCatalogService $bankCatalog,
        private AuditLogService $audit,
        private EmailService $email,
        private SecurityVerificationService $security,
    ) {}

    public function hasOpenWithdrawal(User $user): bool
    {
        return Withdrawal::query()
            ->where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereIn('status', Withdrawal::OPEN_STATUSES)
                    ->orWhereIn('internal_status', Withdrawal::OPEN_INTERNAL);
            })
            ->exists();
    }

    public function assertCanReplace(User $user): void
    {
        if ($this->hasOpenWithdrawal($user)) {
            throw ValidationException::withMessages([
                'bank' => __('You cannot replace your withdrawal bank while a withdrawal request is pending or being processed.'),
            ]);
        }
    }

    public function startReplace(User $user, string $password): void
    {
        $this->assertCanReplace($user);

        $code = $this->security->start(
            $user,
            SecurityVerificationService::PURPOSE_BANK_REPLACE,
            $password,
        );

        $html = View::make('emails.bank-replace-otp', ['code' => $code, 'user' => $user])->render();
        $this->email->sendMailableHtml(
            to: $user->email,
            subject: 'Withdrawal bank change verification code',
            html: $html,
            profile: EmailProfile::NoReply,
            templateKey: 'bank_replace_otp',
        );
    }

    public function verifyOtp(User $user, string $otp): void
    {
        $this->security->verify($user, SecurityVerificationService::PURPOSE_BANK_REPLACE, $otp);
    }

    /**
     * @return array{accountName: string, accountNumber: string, bankCode: string, bankName: string}
     */
    public function resolveNewBank(User $user, string $bankCode, string $bankName, string $accountNumber): array
    {
        $this->assertCanReplace($user);
        $this->security->assertVerified($user, SecurityVerificationService::PURPOSE_BANK_REPLACE);

        if (! $this->rail->isConfigured()) {
            throw new InvalidArgumentException('Bank verification is temporarily unavailable.');
        }

        $this->assertAllowedBank($bankCode, $bankName);

        $resolved = $this->rail->resolveAccount($accountNumber, $bankCode);

        return [
            'accountName' => $resolved['accountName'],
            'accountNumber' => $resolved['accountNumber'],
            'bankCode' => $resolved['bankCode'],
            'bankName' => $bankName,
        ];
    }

    public function confirmReplace(
        User $user,
        string $bankCode,
        string $bankName,
        string $accountNumber,
        ?string $verifiedName = null,
        ?string $ip = null,
        ?string $userAgent = null,
    ): UserBankAccount {
        $this->assertCanReplace($user);
        $this->security->assertVerified($user, SecurityVerificationService::PURPOSE_BANK_REPLACE);

        if (! $this->rail->isConfigured()) {
            throw new InvalidArgumentException('Bank verification is temporarily unavailable.');
        }

        $this->assertAllowedBank($bankCode, $bankName);

        $resolved = $this->rail->resolveAccount($accountNumber, $bankCode);
        $accountNumber = (string) $resolved['accountNumber'];
        $bankCode = (string) $resolved['bankCode'];
        $verifiedName = (string) $resolved['accountName'];

        return DB::transaction(function () use ($user, $bankCode, $bankName, $accountNumber, $verifiedName, $ip, $userAgent) {
            $old = UserBankAccount::query()
                ->where('user_id', $user->id)
                ->where('active', true)
                ->lockForUpdate()
                ->first();

            if ($old) {
                $old->update(['active' => false]);
            }

            $new = UserBankAccount::create([
                'user_id' => $user->id,
                'bank_name' => $bankName,
                'bank_code' => $bankCode,
                'account_number' => $accountNumber,
                'verified_name' => $verifiedName,
                'verified_at' => now(),
                'verified_by' => 'monnify',
                'active' => true,
            ]);

            DB::table('security_verification_codes')
                ->where('user_id', $user->id)
                ->where('purpose', SecurityVerificationService::PURPOSE_BANK_REPLACE)
                ->delete();

            $this->audit->log(
                null,
                'user.bank_replaced',
                $new,
                $old ? [
                    'bank_name' => $old->bank_name,
                    'bank_code' => $old->bank_code,
                    'account_number' => $old->maskedAccountNumber(),
                    'verified_name' => $old->verified_name,
                ] : null,
                [
                    'bank_name' => $new->bank_name,
                    'bank_code' => $new->bank_code,
                    'account_number' => $new->maskedAccountNumber(),
                    'verified_name' => $new->verified_name,
                    'verified_at' => optional($new->verified_at)?->toIso8601String(),
                ],
                $ip,
                [
                    'actor_id' => $user->id,
                    'actor_type' => 'user',
                    'user_agent' => $userAgent,
                    'module' => 'wallet',
                ]
            );

            $html = View::make('emails.bank-replaced', [
                'user' => $user,
                'oldBank' => $old,
                'newBank' => $new,
            ])->render();

            $this->email->sendMailableHtml(
                to: $user->email,
                subject: 'Your withdrawal bank has been updated',
                html: $html,
                profile: EmailProfile::NoReply,
                templateKey: 'bank_replaced',
            );

            return $new;
        });
    }

    private function assertAllowedBank(string $bankCode, string $bankName): void
    {
        $allowed = collect($this->bankCatalog->allowedBanks());

        if ($allowed->contains(fn (array $bank) => $bank['code'] === $bankCode)) {
            return;
        }

        $nameMatch = $allowed->contains(
            fn (array $bank) => mb_strtolower($bank['name']) === mb_strtolower(trim($bankName))
        );

        if ($nameMatch) {
            return;
        }

        throw ValidationException::withMessages([
            'bank_code' => __('This bank is not supported for withdrawals.'),
        ]);
    }
}
