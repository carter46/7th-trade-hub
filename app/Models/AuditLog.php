<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'admin_id',
        'actor_id',
        'actor_type',
        'action',
        'module',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip',
        'user_agent',
        'device',
        'browser',
        'country',
        'reason',
        'correlation_id',
        'request_id',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
