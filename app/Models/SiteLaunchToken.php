<?php

namespace App\Models;

use App\Enums\SiteLaunchContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteLaunchToken extends Model
{
    protected $fillable = [
        'token_hash',
        'context',
        'role',
        'integration_id',
        'bound_email',
        'hub_user_id',
        'site_integration_id',
        'user_tool_id',
        'request_id',
        'nonce',
        'expires_at',
        'consumed_at',
        'ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'context' => SiteLaunchContext::class,
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function hubUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hub_user_id');
    }

    public function siteIntegration(): BelongsTo
    {
        return $this->belongsTo(SiteIntegration::class);
    }

    public function userTool(): BelongsTo
    {
        return $this->belongsTo(UserTool::class);
    }

    public function isUsable(): bool
    {
        return $this->consumed_at === null && $this->expires_at->isFuture();
    }
}
