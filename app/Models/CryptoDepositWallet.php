<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CryptoDepositWallet extends Model
{
    protected $fillable = [
        'coin',
        'network',
        'address',
        'required_confirmations',
        'purpose',
        'owner',
        'notes',
        'label',
        'instructions',
        'live_balance',
        'live_balance_updated_at',
        'live_balance_error',
        'last_deposit_at',
        'last_allocated_at',
        'is_active',
        'is_exchange_managed',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'required_confirmations' => 'integer',
            'live_balance' => 'decimal:10',
            'live_balance_updated_at' => 'datetime',
            'last_deposit_at' => 'datetime',
            'last_allocated_at' => 'datetime',
            'is_active' => 'boolean',
            'is_exchange_managed' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function sellRequests(): HasMany
    {
        return $this->hasMany(CryptoSellRequest::class, 'crypto_deposit_wallet_id');
    }

    public function balanceHistory(): HasMany
    {
        return $this->hasMany(WalletBalanceHistory::class, 'crypto_deposit_wallet_id');
    }

    public function openOrdersCount(): int
    {
        return $this->sellRequests()
            ->whereIn('status', CryptoSellRequest::OPEN_STATUSES)
            ->count();
    }

    public function openOrdersUsingAddress(): int
    {
        $q = $this->openOrdersQuery();

        return app(\App\Modules\Wallet\Services\WalletAllocationService::class)
            ->applyOccupyingOrdersFilter($q)
            ->count();
    }

    /**
     * Crypto reserved by open OTC sell orders on this address.
     */
    public function reservedCrypto(): float
    {
        $sum = $this->openOrdersQuery()
            ->whereIn('status', CryptoSellRequest::OPEN_STATUSES)
            ->sum('amount_crypto');

        return (float) $sum;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\CryptoSellRequest>
     */
    private function openOrdersQuery()
    {
        $variants = app(\App\Modules\Wallet\Services\NetworkRegistry::class)
            ->storageVariants((string) $this->network);
        $placeholders = implode(',', array_fill(0, count($variants), '?'));

        return CryptoSellRequest::query()
            ->where('platform_address', $this->address)
            ->whereRaw('UPPER(coin) = ?', [strtoupper($this->coin)])
            ->whereRaw('LOWER(network) IN ('.$placeholders.')', $variants);
    }

    public function availableCrypto(): float
    {
        $current = (float) ($this->live_balance ?? 0);

        return max(0, $current - $this->reservedCrypto());
    }

    public function capacityLabel(int $maxPerWallet): string
    {
        if (! $this->is_active) {
            return 'Disabled';
        }
        $open = $this->openOrdersUsingAddress();
        if ($open >= $maxPerWallet) {
            return 'Full';
        }

        return 'Available';
    }
}
