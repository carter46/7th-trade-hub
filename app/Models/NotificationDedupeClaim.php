<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationDedupeClaim extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'notification_type',
        'dedupe_key',
        'channel',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
