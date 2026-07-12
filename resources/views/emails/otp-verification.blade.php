<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your email</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #334155; max-width: 480px; margin: 0 auto; padding: 24px;">
    <h1 style="color: #0f172a; font-size: 1.5rem;">Verify your email</h1>
    <p>Use this code to verify your email address on {{ config('app.name') }}:</p>
    <p style="font-size: 1.75rem; font-weight: bold; letter-spacing: 0.25em; color: #16a34a;">{{ $code }}</p>
    <p style="color: #64748b; font-size: 0.875rem;">This code expires in 15 minutes. If you didn't request this, you can ignore this email.</p>
    <p style="color: #64748b; font-size: 0.875rem;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
</body>
</html>
