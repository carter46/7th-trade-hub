<?php

namespace App\Modules\Wallet\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SecurityVerificationService
{
    public const PURPOSE_BANK_REPLACE = 'bank_replace';

    public const PURPOSE_WITHDRAWAL_REQUEST = 'withdrawal_request';

    private const OTP_EXPIRY_MINUTES = 10;

    private const OTP_MAX_ATTEMPTS = 5;

    public function assertPassword(User $user, string $password): void
    {
        if (! $user->hasPasswordSet() || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('The password is incorrect.'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function start(User $user, string $purpose, string $password, ?array $payload = null): string
    {
        $this->assertPassword($user, $password);

        $code = sprintf('%06d', random_int(0, 999999));

        DB::table('security_verification_codes')
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->delete();

        DB::table('security_verification_codes')->insert([
            'user_id' => $user->id,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'attempts' => 0,
            'payload' => $payload !== null ? json_encode($payload) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $code;
    }

    public function verify(User $user, string $purpose, string $otp): void
    {
        $row = $this->activeRow($user, $purpose);

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

        $payload = $row->payload ? json_decode((string) $row->payload, true) : [];
        $payload['otp_verified'] = true;

        DB::table('security_verification_codes')->where('id', $row->id)->update([
            'payload' => json_encode($payload),
            'updated_at' => now(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function consumeVerifiedPayload(User $user, string $purpose): array
    {
        $row = $this->activeRow($user, $purpose);
        $payload = $row ? json_decode((string) $row->payload, true) : null;

        if (! $row || ! is_array($payload) || ! ($payload['otp_verified'] ?? false)) {
            throw ValidationException::withMessages([
                'otp' => __('Verify the email code before continuing.'),
            ]);
        }

        DB::table('security_verification_codes')
            ->where('id', $row->id)
            ->delete();

        unset($payload['otp_verified']);

        return $payload;
    }

    public function assertVerified(User $user, string $purpose): void
    {
        $row = $this->activeRow($user, $purpose);
        $payload = $row ? json_decode((string) $row->payload, true) : null;

        if (! $row || ! is_array($payload) || ! ($payload['otp_verified'] ?? false)) {
            throw ValidationException::withMessages([
                'otp' => __('Verify the email code before continuing.'),
            ]);
        }
    }

    private function activeRow(User $user, string $purpose): ?object
    {
        return DB::table('security_verification_codes')
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->first();
    }
}
