<?php

namespace App\Models;

use App\Enums\UserToolStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class UserTool extends Model
{
    protected $hidden = [
        'admin_password',
        'livechat_password',
    ];

    protected $fillable = [
        'public_id',
        'user_id',
        'order_id',
        'order_item_id',
        'platform_product_id',
        'platform_product_variant_id',
        'instance_sequence',
        'display_name',
        'status',
        'site_url',
        'admin_login_url',
        'admin_email',
        'admin_password',
        'livechat_name',
        'livechat_url',
        'livechat_email',
        'livechat_password',
        'purchased_at',
        'configured_at',
        'expires_at',
        'subscription_end_reason',
        'duration_months',
        'last_synced_at',
    ];

    public const END_REASON_NATURAL = 'natural';

    public const END_REASON_ADMIN_SHUTDOWN = 'admin_shutdown';

    public const END_REASON_ADMIN_ADJUSTED = 'admin_adjusted';

    protected function casts(): array
    {
        return [
            'status' => UserToolStatus::class,
            'admin_password' => 'encrypted',
            'livechat_password' => 'encrypted',
            'purchased_at' => 'datetime',
            'configured_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (UserTool $tool): void {
            if (! $tool->public_id) {
                $tool->public_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(PlatformProduct::class, 'platform_product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(PlatformProductVariant::class, 'platform_product_variant_id');
    }

    public function integration(): HasOne
    {
        return $this->hasOne(UserToolIntegration::class);
    }

    public function domainConnection(): HasOne
    {
        return $this->hasOne(DomainConnection::class);
    }

    /**
     * True when the paid window is still open (independent of stale status).
     */
    public function isWithinPaidWindow(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isFuture();
    }

    /**
     * Launch / password / “active subscription” gate using live expires_at.
     */
    public function isSubscriptionLive(): bool
    {
        if ($this->status === UserToolStatus::Suspended || $this->status === UserToolStatus::PendingSetup) {
            return false;
        }

        if ($this->status === UserToolStatus::Expired) {
            return false;
        }

        if (! $this->isWithinPaidWindow()) {
            return false;
        }

        return $this->status === UserToolStatus::Active;
    }

    /**
     * Status merchants should treat as current (derive expired when clock passed).
     */
    public function effectiveStatus(): UserToolStatus
    {
        if ($this->status === UserToolStatus::Suspended || $this->status === UserToolStatus::PendingSetup) {
            return $this->status;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return UserToolStatus::Expired;
        }

        return $this->status instanceof UserToolStatus
            ? $this->status
            : UserToolStatus::tryFrom((string) $this->status) ?? UserToolStatus::PendingSetup;
    }

    /**
     * True when the site ended via paid-window expiry (not admin shutdown/adjust).
     * Null reason covers legacy rows that expired before this field existed.
     */
    public function endedByNaturalExpiry(): bool
    {
        return $this->subscription_end_reason === null
            || $this->subscription_end_reason === self::END_REASON_NATURAL;
    }

    public function markSubscriptionEnded(string $reason): void
    {
        $this->subscription_end_reason = $reason;
    }

    public function clearSubscriptionEndReason(): void
    {
        $this->subscription_end_reason = null;
    }

    public function isExpiringSoon(int $withinDays = 7): bool
    {
        if (! $this->isSubscriptionLive() || ! $this->expires_at) {
            return false;
        }

        return $this->expires_at->lessThanOrEqualTo(now()->addDays($withinDays))
            && $this->expires_at->isFuture();
    }

    public function canLaunchAdmin(): bool
    {
        if (! $this->isSubscriptionLive()) {
            return false;
        }

        if (! $this->admin_email || ! $this->site_url) {
            return false;
        }

        $integration = $this->integration;

        return $integration
            && $integration->hasCapability(UserToolIntegration::CAP_OWNED_ADMIN_LOGIN)
            && ! in_array($integration->connection_status, ['error'], true);
    }

    public function canRevealAdminPassword(): bool
    {
        return $this->isSubscriptionLive() && is_string($this->admin_password) && $this->admin_password !== '';
    }

    public function canRevealLivechatPassword(): bool
    {
        return $this->isSubscriptionLive()
            && is_string($this->livechat_password)
            && $this->livechat_password !== '';
    }

    public function hasLivechatDetails(): bool
    {
        return filled($this->livechat_name)
            || filled($this->livechat_url)
            || filled($this->livechat_email)
            || filled($this->livechat_password);
    }

    public function hasTutorialDetails(): bool
    {
        $product = $this->product;

        return $product
            && (filled($product->tutorial_url) || filled($product->tutorial_description));
    }

    /**
     * Prefer stored site_url; otherwise derive https://{domain} from the purchase connection.
     */
    public function suggestedSiteUrl(): ?string
    {
        if (filled($this->site_url)) {
            return $this->site_url;
        }

        $fqdn = $this->connectedDomainFqdn();
        if ($fqdn === null) {
            return null;
        }

        return 'https://'.$fqdn;
    }

    public function connectedDomainFqdn(): ?string
    {
        $options = $this->orderItem?->options ?? [];
        $fromOrder = $options['domain_fqdn'] ?? $options['domain_name'] ?? null;
        if (is_string($fromOrder) && trim($fromOrder) !== '') {
            return strtolower(trim($fromOrder));
        }

        $connection = $this->relationLoaded('domainConnection')
            ? $this->getRelation('domainConnection')
            : DomainConnection::query()->where('user_tool_id', $this->id)->first();

        if ($connection && filled($connection->fqdn)) {
            return strtolower(trim((string) $connection->fqdn));
        }

        return null;
    }

    public function resolvedDisplayName(): string
    {
        if ($this->display_name) {
            return $this->display_name;
        }

        $title = $this->product?->title ?? 'Service';

        return $this->instance_sequence > 1
            ? $title.' #'.$this->instance_sequence
            : $title;
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function consumeUrl(): string
    {
        return rtrim((string) $this->site_url, '/').'/auth/7th-tradehub/demo/consume';
    }

    public function healthUrl(): string
    {
        return rtrim((string) $this->site_url, '/').'/api/7th-tradehub/v1/health';
    }

    public function subscriptionSyncUrl(): string
    {
        return rtrim((string) $this->site_url, '/').'/api/7th-tradehub/v1/subscription/sync';
    }
}
