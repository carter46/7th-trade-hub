<?php

namespace App\Mail;

use App\Models\User;
use App\Services\Notifications\NotificationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NotificationMessage $message,
        public User $notifiable
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->message->emailSubject ?: $this->message->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->message->emailView ?: 'emails.notification',
        );
    }
}
