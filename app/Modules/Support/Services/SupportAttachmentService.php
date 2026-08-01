<?php

namespace App\Modules\Support\Services;

use App\Models\SupportAttachment;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SupportAttachmentService
{
    public const DISK = 'local';

    public const MAX_BYTES = 8 * 1024 * 1024;

    public const MAX_FILES = 3;

    public const TTL_HOURS = 72;

    /** @var list<string> */
    public const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'application/pdf',
        'text/plain',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /** @var list<string> */
    public const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'txt', 'doc', 'docx',
    ];

    /**
     * @param  array<int, UploadedFile>|null  $files
     * @return list<SupportAttachment>
     */
    public function storeMany(?array $files, SupportTicket $ticket, User $user, ?SupportTicketReply $reply = null): array
    {
        if ($files === null || $files === []) {
            return [];
        }

        if (count($files) > self::MAX_FILES) {
            throw ValidationException::withMessages([
                'attachments' => __('You may upload at most :max files.', ['max' => self::MAX_FILES]),
            ]);
        }

        $stored = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $stored[] = $this->storeOne($file, $ticket, $user, $reply);
        }

        return $stored;
    }

    public function storeOne(UploadedFile $file, SupportTicket $ticket, User $user, ?SupportTicketReply $reply = null): SupportAttachment
    {
        $this->assertSafe($file);

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = Str::uuid()->toString().'.'.$ext;
        $directory = 'support-evidence/'.$ticket->id;
        $path = $file->storeAs($directory, $filename, self::DISK);

        return SupportAttachment::create([
            'support_ticket_id' => $ticket->id,
            'support_ticket_reply_id' => $reply?->id,
            'user_id' => $user->id,
            'disk' => self::DISK,
            'path' => $path,
            'original_name' => Str::limit($file->getClientOriginalName(), 240, ''),
            'mime' => (string) ($file->getMimeType() ?: 'application/octet-stream'),
            'size' => (int) $file->getSize(),
            'expires_at' => now()->addHours(self::TTL_HOURS),
        ]);
    }

    public function assertSafe(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'attachments' => __('One of the uploads failed. Please try again.'),
            ]);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'attachments' => __('Each file must be under :mb MB.', ['mb' => (int) (self::MAX_BYTES / 1024 / 1024)]),
            ]);
        }

        $name = $file->getClientOriginalName();
        if (substr_count($name, '.') > 1) {
            throw ValidationException::withMessages([
                'attachments' => __('Double extensions are not allowed.'),
            ]);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: '');
        if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'attachments' => __('File type not allowed.'),
            ]);
        }

        $mime = (string) ($file->getMimeType() ?: '');
        $finfoMime = null;
        if (is_readable($file->getRealPath())) {
            $finfoMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()) ?: null;
        }

        $resolved = $finfoMime ?: $mime;
        if (! in_array($resolved, self::ALLOWED_MIMES, true)) {
            throw ValidationException::withMessages([
                'attachments' => __('File content type not allowed.'),
            ]);
        }
    }

    public function pruneExpired(): int
    {
        $count = 0;

        SupportAttachment::query()
            ->where('expires_at', '<', now())
            ->orderBy('id')
            ->chunkById(100, function ($rows) use (&$count) {
                foreach ($rows as $attachment) {
                    /** @var SupportAttachment $attachment */
                    $attachment->deleteFile();
                    $attachment->delete();
                    $count++;
                }
            });

        return $count;
    }
}
