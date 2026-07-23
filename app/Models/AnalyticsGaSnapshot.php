<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsGaSnapshot extends Model
{
    protected $fillable = [
        'metric',
        'dimension',
        'period_start',
        'period_end',
        'payload',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'payload' => 'array',
            'fetched_at' => 'datetime',
        ];
    }
}
