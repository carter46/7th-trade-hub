<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $message->emailSubject ?: $message->title }}</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #111; max-width: 560px; margin: 0 auto; padding: 24px;">
    <p style="margin: 0 0 8px; font-size: 14px; color: #666;">{{ config('app.name') }}</p>
    <h1 style="font-size: 20px; margin: 0 0 12px;">{{ $message->title }}</h1>
    @if($message->body)
        <p style="margin: 0 0 16px;">{{ $message->body }}</p>
    @endif
    @if($message->actionUrl)
        <p style="margin: 0 0 24px;">
            <a href="{{ $message->actionUrl }}" style="display: inline-block; background: #111; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 6px;">
                View details
            </a>
        </p>
    @endif
    <p style="margin: 0; font-size: 12px; color: #888;">Hello {{ $notifiable->name }},</p>
</body>
</html>
