<?php

namespace App\Models;

use App\Enums\SiteIntegrationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteIntegration extends Model
{
    public const CAP_HEALTH = 'health';

    public const CAP_DEMO_USER_LOGIN = 'demo_user_login';

    public const CAP_DEMO_ADMIN_LOGIN = 'demo_admin_login';

    protected $hidden = [
        'client_secret',
        'webhook_secret',
    ];

    protected $fillable = [
        'platform_product_id',
        'name',
        'base_url',
        'demo_user_email',
        'demo_admin_email',
        'integration_id',
        'client_id',
        'client_secret',
        'webhook_secret',
        'capabilities',
        'status',
        'connection_status',
        'last_checked_at',
        'last_error',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => SiteIntegrationStatus::class,
            'capabilities' => 'array',
            'client_secret' => 'encrypted',
            'webhook_secret' => 'encrypted',
            'last_checked_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PlatformProduct::class, 'platform_product_id');
    }

    public function checkLogs(): HasMany
    {
        return $this->hasMany(SiteIntegrationCheckLog::class, 'owner_id')
            ->where('owner_type', 'demo');
    }

    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities ?? [], true);
    }

    public function isActive(): bool
    {
        return $this->status === SiteIntegrationStatus::Active;
    }

    public function consumeUrl(): string
    {
        return rtrim($this->base_url, '/').'/auth/7th-tradehub/demo/consume';
    }

    public function healthUrl(): string
    {
        return rtrim($this->base_url, '/').'/api/7th-tradehub/v1/health';
    }

    /**
     * @return list<string>
     */
    public static function defaultCapabilities(): array
    {
        return [
            self::CAP_HEALTH,
            self::CAP_DEMO_USER_LOGIN,
            self::CAP_DEMO_ADMIN_LOGIN,
        ];
    }
}
