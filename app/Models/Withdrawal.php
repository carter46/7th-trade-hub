<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Withdrawal extends Model
{
    protected $guarded = ['id'];

    public const OPEN_STATUSES = ['pending', 'approved', 'processing'];

    public const INTERNAL_AWAITING_PROVIDER_AUTH = 'awaiting_provider_authorization';

    public const OPEN_INTERNAL = [
        'pending_review',
        'approved',
        'processing',
        self::INTERNAL_AWAITING_PROVIDER_AUTH,
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'account_number' => 'encrypted',
            'provider_meta' => 'array',
            'provider_auth_attempts' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(UserBankAccount::class, 'user_bank_account_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function timelineEvents(): MorphMany
    {
        return $this->morphMany(PaymentTimelineEvent::class, 'subject')->orderBy('occurred_at');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true)
            || in_array((string) $this->internal_status, self::OPEN_INTERNAL, true);
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing' || $this->internal_status === 'processing';
    }

    public function needsProviderAuthorization(): bool
    {
        if ($this->isProviderAuthorizationExpired()) {
            return false;
        }

        if ($this->internal_status === self::INTERNAL_AWAITING_PROVIDER_AUTH) {
            return true;
        }

        if ($this->isTerminal()) {
            return false;
        }

        return strtoupper((string) $this->provider_status) === 'PENDING_AUTHORIZATION';
    }

    public function isProviderAuthorizationExpired(): bool
    {
        return strtoupper((string) $this->provider_status) === 'EXPIRED';
    }

    public function canBeRejectedByAdmin(): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        if ($this->internal_status === self::INTERNAL_AWAITING_PROVIDER_AUTH) {
            return true;
        }

        return in_array($this->status, ['pending', 'approved'], true)
            || in_array($this->internal_status, ['pending_review', 'approved'], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['completed', 'rejected', 'failed'], true)
            || in_array($this->internal_status, ['completed', 'failed'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function providerInitiateSnapshot(): array
    {
        return (array) (($this->provider_meta ?? [])['initiate'] ?? []);
    }

    public function maskedAccountNumber(): string
    {
        $num = (string) $this->account_number;
        if (strlen($num) <= 4) {
            return str_repeat('*', strlen($num));
        }

        return str_repeat('*', max(0, strlen($num) - 4)).substr($num, -4);
    }
}
