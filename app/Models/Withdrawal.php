<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Withdrawal extends Model
{
    protected $guarded = ['id'];

    public const OPEN_STATUSES = ['pending', 'approved', 'processing'];

    public const OPEN_INTERNAL = ['pending_review', 'approved', 'processing'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'account_number' => 'encrypted',
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

    public function maskedAccountNumber(): string
    {
        $num = (string) $this->account_number;
        if (strlen($num) <= 4) {
            return str_repeat('*', strlen($num));
        }

        return str_repeat('*', max(0, strlen($num) - 4)).substr($num, -4);
    }
}
