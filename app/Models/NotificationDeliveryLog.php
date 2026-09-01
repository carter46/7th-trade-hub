<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationDeliveryLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event',
        'notification_type',
        'profile',
        'recipient',
        'channel',
        'status',
        'dedupe_key',
        'failure_reason',
        'meta',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
