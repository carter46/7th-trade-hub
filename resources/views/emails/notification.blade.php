@include('emails.layouts.user', [
    'message' => $message,
    'notifiable' => $notifiable,
    'branding' => app(\App\Services\Branding\SiteBrandingRepository::class)->all(),
    'contact' => app(\App\Services\Communications\Contact\PlatformContactRepository::class)->all(),
    'context' => $message->meta['email_context'] ?? [],
])
