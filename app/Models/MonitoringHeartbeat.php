<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoringHeartbeat extends Model
{
    protected $fillable = [
        'key',
        'payload',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'recorded_at' => 'datetime',
        ];
    }
}
