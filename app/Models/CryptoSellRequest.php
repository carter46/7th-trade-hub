<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CryptoSellRequest extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_WAITING_DEPOSIT = 'waiting_deposit';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_VERIFYING = 'verifying';

    public const STATUS_UNDERPAID = 'underpaid_waiting_customer';

    public const STATUS_OVERPAID = 'overpaid_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    /** Statuses that can still receive deposits / matching. */
    public const OPEN_STATUSES = [
        self::STATUS_WAITING_DEPOSIT,
        self::STATUS_SUBMITTED,
        self::STATUS_VERIFYING,
        self::STATUS_UNDERPAID,
        self::STATUS_OVERPAID,
    ];

    /** Statuses eligible for admin approve & credit. */
    public const APPROVABLE_STATUSES = [
        self::STATUS_WAITING_DEPOSIT,
        self::STATUS_SUBMITTED,
        self::STATUS_VERIFYING,
        self::STATUS_UNDERPAID,
        self::STATUS_OVERPAID,
        'pending', // legacy
    ];

    public const TERMINAL_STATUSES = [
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_EXPIRED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'tracking_code',
        'user_id',
        'wallet_id',
        'crypto_deposit_wallet_id',
        'coin',
        'network',
        'amount_crypto',
        'amount_crypto_base',
        'amount_usd',
        'quoted_rate_ngn',
        'market_rate_ngn',
        'spread_ngn',
        'coin_usd_price',
        'pricing_source',
        'expected_ngn',
        'credit_ngn_override',
        'quoted_at',
        'expires_at',
        'status',
        'tx_hash',
        'platform_address',
        'required_confirmations',
        'amount_match_status',
        'confirmations_observed',
        'wallet_funding_id',
        'admin_notes',
        'verification_checklist',
    ];

    protected function casts(): array
    {
        return [
            'amount_crypto' => 'decimal:10',
            'amount_crypto_base' => 'decimal:10',
            'amount_usd' => 'decimal:4',
            'quoted_rate_ngn' => 'decimal:2',
            'market_rate_ngn' => 'decimal:4',
            'spread_ngn' => 'decimal:4',
            'coin_usd_price' => 'decimal:4',
            'expected_ngn' => 'decimal:2',
            'credit_ngn_override' => 'decimal:2',
            'quoted_at' => 'datetime',
            'expires_at' => 'datetime',
            'required_confirmations' => 'integer',
            'confirmations_observed' => 'integer',
            'verification_checklist' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (! filled($model->tracking_code)) {
                $model->tracking_code = self::generateTrackingCode();
            }
        });
    }

    public static function generateTrackingCode(?\DateTimeInterface $when = null): string
    {
        $when ??= now();
        $date = $when->format('Ymd');

        for ($i = 0; $i < 12; $i++) {
            $code = 'OTC-'.$date.'-'.strtoupper(Str::random(6));
            if (! self::query()->where('tracking_code', $code)->exists()) {
                return $code;
            }
        }

        return 'OTC-'.$date.'-'.strtoupper(Str::random(8));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function walletFunding(): BelongsTo
    {
        return $this->belongsTo(WalletFunding::class);
    }

    public function depositWallet(): BelongsTo
    {
        return $this->belongsTo(CryptoDepositWallet::class, 'crypto_deposit_wallet_id');
    }

    public function incomingTransactions(): HasMany
    {
        return $this->hasMany(IncomingCryptoTransaction::class, 'matched_order_id');
    }

    public function isQuoteExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function creditAmountNgn(): float
    {
        if ($this->credit_ngn_override !== null) {
            return (float) $this->credit_ngn_override;
        }

        return (float) $this->expected_ngn;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true)
            || $this->status === 'pending';
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }

    public function isApprovable(): bool
    {
        return in_array($this->status, self::APPROVABLE_STATUSES, true);
    }

    /**
     * UX stage for the live tracking page.
     */
    public function trackingStage(): string
    {
        if ($this->status === self::STATUS_WAITING_DEPOSIT && $this->isQuoteExpired()) {
            return 'expired';
        }

        return match ($this->status) {
            self::STATUS_WAITING_DEPOSIT, 'pending' => 'waiting_deposit',
            self::STATUS_UNDERPAID => 'underpaid',
            self::STATUS_OVERPAID => 'overpaid',
            self::STATUS_APPROVED => 'approved',
            self::STATUS_REJECTED => 'rejected',
            self::STATUS_EXPIRED => 'expired',
            self::STATUS_CANCELLED => 'cancelled',
            self::STATUS_VERIFYING => 'awaiting_admin',
            self::STATUS_SUBMITTED => $this->confirmationsMet() ? 'awaiting_admin' : 'deposit_detected',
            default => 'waiting_deposit',
        };
    }

    public function confirmationsMet(): bool
    {
        $required = max(1, (int) ($this->required_confirmations ?? 1));
        $observed = (int) ($this->confirmations_observed ?? 0);

        return $observed >= $required;
    }

    public function pollIntervalMs(): int
    {
        return match ($this->trackingStage()) {
            'waiting_deposit' => 4000,
            'deposit_detected', 'underpaid' => 6000,
            'awaiting_admin', 'overpaid' => 10000,
            default => 0,
        };
    }

    public function expireIfNeeded(): bool
    {
        if ($this->status !== self::STATUS_WAITING_DEPOSIT && $this->status !== 'pending') {
            return false;
        }
        if (! $this->isQuoteExpired()) {
            return false;
        }

        $this->update(['status' => self::STATUS_EXPIRED]);

        return true;
    }
}
