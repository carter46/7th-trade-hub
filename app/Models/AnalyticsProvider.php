<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsProvider extends Model
{
    public const PROVIDER_GOOGLE_ANALYTICS = 'google_analytics';

    public const PROVIDER_MICROSOFT_CLARITY = 'microsoft_clarity';

    protected $fillable = [
        'provider',
        'enabled',
        'credentials',
        'status',
        'last_sync_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'credentials' => 'encrypted:array',
            'last_sync_at' => 'datetime',
        ];
    }

    public static function forProvider(string $provider): self
    {
        return static::query()->firstOrCreate(
            ['provider' => $provider],
            ['enabled' => false, 'status' => 'idle']
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function credential(string $key, mixed $default = null): mixed
    {
        $credentials = $this->credentials ?? [];

        return $credentials[$key] ?? $default;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function mergeCredentials(array $values): void
    {
        $this->credentials = array_merge($this->credentials ?? [], $values);
    }
}
