<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailDeliveryAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'correlation_id',
        'provider',
        'success',
        'recipient',
        'subject',
        'template_key',
        'purpose',
        'http_status',
        'provider_error_code',
        'error_message',
        'response_body',
        'message_id',
        'request_id',
        'latency_ms',
        'delivery_status',
        'is_fallback',
        'attempt_number',
        'meta',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'is_fallback' => 'boolean',
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
