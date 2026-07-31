<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset your password</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #334155; max-width: 480px; margin: 0 auto; padding: 24px;">
    <h1 style="color: #0f172a; font-size: 1.5rem;">Reset your password</h1>
    <p>We received a request to reset your password for {{ config('app.name') }}.</p>
    <p>
        <a href="{{ $url }}" style="display: inline-block; padding: 12px 20px; background: #16a34a; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
            Reset password
        </a>
    </p>
    <p style="color: #64748b; font-size: 0.875rem;">This link expires in {{ $count }} minutes. If you didn't request a reset, you can ignore this email.</p>
    <p style="color: #64748b; font-size: 0.875rem;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
</body>
</html>
