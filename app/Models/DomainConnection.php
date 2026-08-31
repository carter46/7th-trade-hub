<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainConnection extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'order_id',
        'order_item_id',
        'user_tool_id',
        'fqdn',
        'claim_key',
        'nameservers_at_scan',
        'nameservers_last_seen',
        'required_nameservers',
        'verification_status',
        'acknowledged_at',
        'verified_at',
        'last_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'nameservers_at_scan' => 'array',
            'nameservers_last_seen' => 'array',
            'required_nameservers' => 'array',
            'acknowledged_at' => 'datetime',
            'verified_at' => 'datetime',
            'last_checked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function userTool(): BelongsTo
    {
        return $this->belongsTo(UserTool::class);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === self::STATUS_VERIFIED;
    }

    public function isPending(): bool
    {
        return $this->verification_status === self::STATUS_PENDING;
    }

    /**
     * @return list<string>
     */
    public function nameserversAtScanList(): array
    {
        return $this->stringList($this->nameservers_at_scan);
    }

    /**
     * @return list<string>
     */
    public function nameserversLastSeenList(): array
    {
        return $this->stringList($this->nameservers_last_seen);
    }

    /**
     * @return list<string>
     */
    public function requiredNameserverList(): array
    {
        return $this->stringList($this->required_nameservers);
    }

    /**
     * Display NS: last check wins, else scan snapshot.
     *
     * @return list<string>
     */
    public function displayNameserverList(): array
    {
        $last = $this->nameserversLastSeenList();

        return $last !== [] ? $last : $this->nameserversAtScanList();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeForUser($query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Active claims that block other users from connecting the same FQDN.
     * Only paid orders count — unpaid gateway checkouts must not lock a domain.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeActiveClaim($query): void
    {
        $query->whereIn('verification_status', [self::STATUS_PENDING, self::STATUS_VERIFIED])
            ->whereHas('order', fn ($order) => $order->where('status', 'paid'));
    }

    /**
     * @param  mixed  $list
     * @return list<string>
     */
    private function stringList(mixed $list): array
    {
        if (! is_array($list)) {
            return [];
        }

        return array_values(array_filter($list, fn ($ns) => is_string($ns) && $ns !== ''));
    }
}
