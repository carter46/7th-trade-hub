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
        'estimated_holdings',
        'estimated_holdings_at',
        'last_deposit_at',
        'last_allocated_at',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'required_confirmations' => 'integer',
            'estimated_holdings' => 'decimal:10',
            'estimated_holdings_at' => 'datetime',
            'last_deposit_at' => 'datetime',
            'last_allocated_at' => 'datetime',
            'is_active' => 'boolean',
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

    public function openOrdersCount(): int
    {
        return $this->sellRequests()
            ->whereIn('status', CryptoSellRequest::OPEN_STATUSES)
            ->count();
    }

    public function openOrdersUsingAddress(): int
    {
        $q = CryptoSellRequest::query()
            ->where('platform_address', $this->address)
            ->whereRaw('UPPER(coin) = ?', [strtoupper($this->coin)])
            ->whereRaw('LOWER(network) = ?', [strtolower((string) $this->network)]);

        return app(\App\Modules\Wallet\Services\WalletAllocationService::class)
            ->applyOccupyingOrdersFilter($q)
            ->count();
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
