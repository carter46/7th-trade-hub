@php
    $siteName = $branding['site_name'] ?? config('app.name');
    $logoMediaId = $branding['logo_dark_media_id'] ?? $branding['logo_light_media_id'] ?? null;
    $logoUrl = email_branding_logo_url($logoMediaId ? (int) $logoMediaId : null);
    $adminUrl = \Illuminate\Support\Facades\Route::has('admin.dashboard') ? route('admin.dashboard') : config('app.url');
    $preheader = \Illuminate\Support\Str::limit(trim(strip_tags((string) ($message->body ?? ''))), 140, '…');
    $headerTitle = $message->title ?? 'Admin alert';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $message->emailSubject ?: $message->title }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:#111827;">
@if($preheader !== '')
<div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">{{ $preheader }}</div>
@endif
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f8;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 28px;border-bottom:1px solid #e5e7eb;background:#111827;color:#ffffff;">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" width="160" height="36" style="max-height:36px;max-width:160px;height:auto;width:auto;display:block;margin-bottom:8px;border:0;outline:none;text-decoration:none;">
                        @endif
                        <div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;opacity:.8;">Admin alert</div>
                        <div style="font-size:18px;font-weight:700;line-height:1.3;margin-top:4px;">{{ $siteName }}</div>
                        <div style="font-size:14px;font-weight:600;line-height:1.4;margin-top:6px;opacity:.95;">{{ $headerTitle }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;">
                        <h1 style="margin:0 0 12px;font-size:22px;line-height:1.3;color:#111827;">{{ $message->title }}</h1>
                        @if($message->body)
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#374151;">{{ $message->body }}</p>
                        @endif
                        @isset($context['order_reference'])
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:16px 0;font-size:14px;">
                                <tr><td style="padding:4px 0;color:#6b7280;">Order</td><td style="padding:4px 0;font-weight:600;">{{ $context['order_reference'] }}</td></tr>
                                @if(!empty($context['total_amount']))
                                    <tr><td style="padding:4px 0;color:#6b7280;">Amount</td><td style="padding:4px 0;font-weight:600;">₦{{ number_format((float) $context['total_amount'], 2) }}</td></tr>
                                @endif
                            </table>
                        @endisset
                        @if(!empty($context['lines']) && is_array($context['lines']))
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:8px 0 16px;font-size:14px;border-collapse:collapse;">
                                <tr>
                                    <td colspan="2" style="padding:8px 0 6px;font-weight:700;color:#111827;border-bottom:1px solid #e5e7eb;">Items</td>
                                </tr>
                                @foreach($context['lines'] as $line)
                                    <tr>
                                        <td style="padding:10px 0;vertical-align:top;border-bottom:1px solid #f3f4f6;">
                                            <div style="font-weight:600;">{{ $line['title'] ?? 'Item' }}</div>
                                            @if(!empty($line['subtitle']))
                                                <div style="margin-top:2px;font-size:13px;color:#6b7280;">{{ $line['subtitle'] }}</div>
                                            @endif
                                        </td>
                                        <td style="padding:10px 0;vertical-align:top;text-align:right;font-weight:600;border-bottom:1px solid #f3f4f6;white-space:nowrap;">
                                            ₦{{ number_format((float) ($line['line_total'] ?? 0), 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif
                        @if($message->actionUrl)
                            <p style="margin:20px 0 0;">
                                <a href="{{ $message->actionUrl }}" style="display:inline-block;background:#111827;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:600;font-size:14px;">Open in admin</a>
                            </p>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 28px;border-top:1px solid #e5e7eb;background:#f9fafb;font-size:12px;color:#6b7280;">
                        {{ $siteName }} · <a href="{{ $adminUrl }}" style="color:#111827;">Admin dashboard</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
