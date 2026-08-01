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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class BankAccountService
{
    public const PURPOSE_REPLACE = 'bank_replace';

    private const OTP_EXPIRY_MINUTES = 10;

    private const OTP_MAX_ATTEMPTS = 5;

    public function __construct(
        private PaymentRailInterface $rail,
        private AuditLogService $audit,
        private EmailService $email,
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

        if (! $user->hasPasswordSet() || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('The password is incorrect.'),
            ]);
        }

        $code = sprintf('%06d', random_int(0, 999999));

        DB::table('security_verification_codes')->where('user_id', $user->id)->where('purpose', self::PURPOSE_REPLACE)->delete();
        DB::table('security_verification_codes')->insert([
            'user_id' => $user->id,
            'purpose' => self::PURPOSE_REPLACE,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'attempts' => 0,
            'payload' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
        $row = DB::table('security_verification_codes')
            ->where('user_id', $user->id)
            ->where('purpose', self::PURPOSE_REPLACE)
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->first();

        if (! $row) {
            throw ValidationException::withMessages([
                'otp' => __('The verification code has expired. Please request a new one.'),
            ]);
        }

        if ($row->attempts >= self::OTP_MAX_ATTEMPTS) {
            DB::table('security_verification_codes')->where('id', $row->id)->delete();
            throw ValidationException::withMessages([
                'otp' => __('Too many attempts. Please start again.'),
            ]);
        }

        if (! Hash::check($otp, $row->code_hash)) {
            DB::table('security_verification_codes')->where('id', $row->id)->increment('attempts');
            throw ValidationException::withMessages([
                'otp' => __('The verification code is invalid.'),
            ]);
        }

        DB::table('security_verification_codes')->where('id', $row->id)->update([
            'payload' => json_encode(['otp_verified' => true]),
            'updated_at' => now(),
        ]);
    }

    /**
     * @return array{accountName: string, accountNumber: string, bankCode: string, bankName: string}
     */
    public function resolveNewBank(User $user, string $bankCode, string $bankName, string $accountNumber): array
    {
        $this->assertCanReplace($user);
        $this->assertOtpVerified($user);

        if (! $this->rail->isConfigured()) {
            throw new InvalidArgumentException('Bank verification is temporarily unavailable.');
        }

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
        $this->assertOtpVerified($user);

        if (! $this->rail->isConfigured()) {
            throw new InvalidArgumentException('Bank verification is temporarily unavailable.');
        }

        // Never trust client name/account — re-resolve with Monnify at confirm time.
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
                ->where('purpose', self::PURPOSE_REPLACE)
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

    private function assertOtpVerified(User $user): void
    {
        $row = DB::table('security_verification_codes')
            ->where('user_id', $user->id)
            ->where('purpose', self::PURPOSE_REPLACE)
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->first();

        $payload = $row ? json_decode((string) $row->payload, true) : null;
        if (! $row || ! ($payload['otp_verified'] ?? false)) {
            throw ValidationException::withMessages([
                'otp' => __('Verify the email code before continuing.'),
            ]);
        }
    }
}
