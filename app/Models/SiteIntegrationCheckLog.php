<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteIntegrationCheckLog extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'direction',
        'ok',
        'http_status',
        'message',
        'payload_summary',
    ];

    protected function casts(): array
    {
        return [
            'ok' => 'boolean',
            'payload_summary' => 'array',
        ];
    }
}
