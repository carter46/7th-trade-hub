<p style="font-family: sans-serif; font-size: 15px; color: #222;">
    Your withdrawal bank has been updated on {{ config('app.name') }}.
</p>
@if ($oldBank)
<p style="font-family: sans-serif; font-size: 14px;">
    <strong>Old bank:</strong> {{ $oldBank->bank_name }} {{ $oldBank->maskedAccountNumber() }}
</p>
@endif
<p style="font-family: sans-serif; font-size: 14px;">
    <strong>New bank:</strong> {{ $newBank->bank_name }} {{ $newBank->maskedAccountNumber() }} ({{ $newBank->verified_name }})
</p>
<p style="font-family: sans-serif; font-size: 14px;">
    <strong>Time:</strong> {{ now()->toDateTimeString() }} UTC
</p>
<p style="font-family: sans-serif; font-size: 13px; color: #666;">
    If this wasn't you, contact support immediately.
</p>
