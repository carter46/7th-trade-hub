<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DomainProvider extends Model
{
    public const HEALTH_HEALTHY = 'healthy';

    public const HEALTH_DEGRADED = 'degraded';

    public const HEALTH_UNAVAILABLE = 'unavailable';

    public const HEALTH_UNKNOWN = 'unknown';

    protected $fillable = [
        'key',
        'display_name',
        'adapter_class',
        'enabled',
        'is_default',
        'fallback_priority',
        'sandbox',
        'capabilities',
        'credentials',
        'health_status',
        'last_health_check_at',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'is_default' => 'boolean',
            'sandbox' => 'boolean',
            'capabilities' => 'array',
            'credentials' => 'encrypted:array',
            'last_health_check_at' => 'datetime',
        ];
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(DomainQuote::class, 'provider_key', 'key');
    }

    public function hasCredentials(): bool
    {
        $keys = config('domains.providers.'.$this->key.'.credential_keys', ['username', 'api_token']);
        $creds = $this->credentials ?? [];

        foreach ($keys as $key) {
            if (! filled($creds[$key] ?? null)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, string>
     */
    public function credentialLabels(): array
    {
        return config('domains.providers.'.$this->key.'.credential_labels', [
            'username' => 'Username',
            'api_token' => 'API token',
        ]);
    }
}
