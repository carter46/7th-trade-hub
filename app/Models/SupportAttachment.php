<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class SupportAttachment extends Model
{
    protected $fillable = [
        'support_ticket_id',
        'support_ticket_reply_id',
        'user_id',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'size' => 'integer',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(SupportTicketReply::class, 'support_ticket_reply_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function temporaryUrl(int $minutes = 20): string
    {
        return URL::temporarySignedRoute(
            'dashboard.support.attachments.download',
            now()->addMinutes($minutes),
            ['attachment' => $this->id]
        );
    }

    public function deleteFile(): void
    {
        if ($this->path && Storage::disk($this->disk)->exists($this->path)) {
            Storage::disk($this->disk)->delete($this->path);
        }
    }
}
