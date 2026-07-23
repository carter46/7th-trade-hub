<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    public const CATEGORIES = [
        'payment', 'withdrawal', 'wallet', 'marketplace', 'listing',
        'order', 'kyc', 'crypto_sell', 'technical', 'other',
    ];

    protected $fillable = [
        'user_id',
        'category',
        'subject',
        'body',
        'status',
        'priority',
        'assigned_to',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class);
    }
}
