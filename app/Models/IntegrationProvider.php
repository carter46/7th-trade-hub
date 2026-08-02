<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationProvider extends Model
{
    public const BREVO = 'brevo';

    public const LARAVEL_MAIL = 'laravel_mail';

    public const SMARTSUPP = 'smartsupp';

    public const JIVO = 'jivo';

    public const CHATWAY = 'chatway';

    public const GOOGLE_ANALYTICS = 'google_analytics';

    public const MICROSOFT_CLARITY = 'microsoft_clarity';

    public const GOOGLE_TAG_MANAGER = 'google_tag_manager';

    public const META_PIXEL = 'meta_pixel';

    public const GOOGLE_IDENTITY = 'google_identity';

    public const MONNIFY = 'monnify';

    public const BLOCKCHAIN_MONITORING = 'blockchain_monitoring';

    protected $fillable = [
        'provider',
        'enabled',
        'credentials',
        'status',
        'last_sync_at',
        'last_tested_at',
        'last_success_at',
        'last_error_at',
        'last_error',
        'success_count',
        'failure_count',
        'avg_latency_ms',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'credentials' => 'encrypted:array',
            'last_sync_at' => 'datetime',
            'last_tested_at' => 'datetime',
            'last_success_at' => 'datetime',
            'last_error_at' => 'datetime',
            'meta' => 'array',
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

    /**
     * Masked form of a stored credential for UI placeholders (not for submission).
     */
    public function maskedCredential(string $key): ?string
    {
        $value = $this->credential($key);

        return is_string($value) ? mask_secret($value) : null;
    }

    public function recordSuccess(?int $latencyMs = null): void
    {
        $this->success_count = (int) $this->success_count + 1;
        $this->last_success_at = now();
        $this->last_tested_at = now();
        $this->status = 'connected';
        $this->last_error = null;
        if ($latencyMs !== null) {
            $prev = $this->avg_latency_ms;
            $this->avg_latency_ms = $prev
                ? (int) round(($prev + $latencyMs) / 2)
                : $latencyMs;
        }
        $this->save();
    }

    public function recordFailure(string $error): void
    {
        $this->failure_count = (int) $this->failure_count + 1;
        $this->last_error = mb_substr($error, 0, 2000);
        $this->last_error_at = now();
        $this->last_tested_at = now();
        $this->status = 'error';
        $this->save();
    }

    public function successRate(): ?float
    {
        $total = (int) $this->success_count + (int) $this->failure_count;
        if ($total === 0) {
            return null;
        }

        return round(((int) $this->success_count / $total) * 100, 1);
    }
}
