<?php

namespace App\Modules\Admin\Services;

use App\Models\AuditLog;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FinancialAuditLog
{
    public function __construct(
        private AuditLogService $audit
    ) {}

    public function logMoneyAction(
        ?int $adminId,
        string $action,
        ?Model $model,
        ?Wallet $walletBefore,
        ?Wallet $walletAfter,
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $requestId = null,
        ?array $extra = null,
    ): AuditLog {
        $payload = array_filter([
            'wallet_before' => $walletBefore ? [
                'wallet_id' => $walletBefore->id,
                'balance' => (string) $walletBefore->balance,
                'locked_balance' => (string) $walletBefore->locked_balance,
            ] : null,
            'wallet_after' => $walletAfter ? [
                'wallet_id' => $walletAfter->id,
                'balance' => (string) $walletAfter->balance,
                'locked_balance' => (string) $walletAfter->locked_balance,
            ] : null,
            'request_id' => $requestId ?? Str::uuid()->toString(),
            'user_agent' => $userAgent ? substr($userAgent, 0, 255) : null,
            'entity' => $model ? [
                'type' => $model::class,
                'id' => $model->getKey(),
            ] : null,
            'extra' => $extra,
        ]);

        return $this->audit->log($adminId, $action, $model, null, $payload, $ip);
    }

    public static function sanitizeWithdrawal(array $data): array
    {
        unset($data['account_number']);

        return $data;
    }
}
