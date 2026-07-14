@props([
    'status' => 'default',
])

@php
    $map = [
        'default' => 'bg-muted text-text-secondary',
        'pending' => 'bg-warning/20 text-warning',
        'approved' => 'bg-success/20 text-success',
        'completed' => 'bg-success/20 text-success',
        'rejected' => 'bg-danger/20 text-danger',
        'failed' => 'bg-danger/20 text-danger',
        'locked' => 'bg-blue-500/20 text-blue-300',
        'released' => 'bg-success/20 text-success',
        'refunded' => 'bg-muted text-text-secondary',
        'active' => 'bg-success/20 text-success',
        'suspended' => 'bg-danger/20 text-danger',
        'info' => 'bg-blue-500/20 text-blue-300',
        'warning' => 'bg-warning/20 text-warning',
        'success' => 'bg-success/20 text-success',
        'danger' => 'bg-danger/20 text-danger',
    ];
    $key = strtolower((string) $status);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' . ($map[$key] ?? $map['default'])]) }}>
    {{ $slot->isEmpty() ? ucfirst($key) : $slot }}
</span>
