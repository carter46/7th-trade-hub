<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailIdentity extends Model
{
    public const GENERAL = 'general';

    public const SUPPORT = 'support';

    public const SALES = 'sales';

    public const SECURITY = 'security';

    public const BILLING = 'billing';

    public const NOREPLY = 'noreply';

    protected $fillable = [
        'profile',
        'from_name',
        'from_email',
        'reply_to_email',
        'notify_to_email',
        'is_default',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'enabled' => 'boolean',
        ];
    }

    public static function forProfile(string $profile): ?self
    {
        return static::query()
            ->where('profile', $profile)
            ->where('enabled', true)
            ->first();
    }

    public static function defaultIdentity(): ?self
    {
        return static::query()->where('is_default', true)->where('enabled', true)->first()
            ?? static::forProfile(self::NOREPLY)
            ?? static::query()->where('enabled', true)->first();
    }
}
