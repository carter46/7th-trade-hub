<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserToolIntegration extends Model
{
    public const CAP_HEALTH = 'health';

    public const CAP_SUBSCRIPTION_SYNC = 'subscription_sync';

    public const CAP_SHUTDOWN_ON_EXPIRY = 'shutdown_on_expiry';

    public const CAP_OWNED_ADMIN_LOGIN = 'owned_admin_login';

    protected $hidden = [
        'client_secret',
        'webhook_secret',
    ];

    protected $fillable = [
        'user_tool_id',
        'integration_id',
        'client_id',
        'client_secret',
        'webhook_secret',
        'capabilities',
        'connection_status',
        'last_checked_at',
        'last_error',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'capabilities' => 'array',
            'client_secret' => 'encrypted',
            'webhook_secret' => 'encrypted',
            'last_checked_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function userTool(): BelongsTo
    {
        return $this->belongsTo(UserTool::class);
    }

    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities ?? [], true);
    }

    /**
     * @return list<string>
     */
    public static function defaultCapabilities(): array
    {
        return [
            self::CAP_HEALTH,
            self::CAP_SUBSCRIPTION_SYNC,
            self::CAP_SHUTDOWN_ON_EXPIRY,
            self::CAP_OWNED_ADMIN_LOGIN,
        ];
    }
}
