<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsKpiSnapshot extends Model
{
    protected $fillable = [
        'kpi_key',
        'period',
        'value',
        'meta',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'meta' => 'array',
            'captured_at' => 'datetime',
        ];
    }
}
