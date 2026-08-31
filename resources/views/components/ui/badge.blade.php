@props([
    'status' => 'default',
])

@php
    $map = [
        'default' => 'bg-muted text-text-secondary',
        'neutral' => 'bg-muted text-text-secondary',
        'pending' => 'bg-warning/20 text-warning',
        'verified' => 'bg-success/20 text-success',
        'approved' => 'bg-success/20 text-success',
        'completed' => 'bg-success/20 text-success',
        'rejected' => 'bg-danger/20 text-danger',
        'failed' => 'bg-danger/20 text-danger',
        'locked' => 'bg-primary/15 text-primary',
        'released' => 'bg-success/20 text-success',
        'refunded' => 'bg-muted text-text-secondary',
        'active' => 'bg-success/20 text-success',
        'suspended' => 'bg-danger/20 text-danger',
        'info' => 'bg-primary/15 text-primary',
        'warning' => 'bg-warning/20 text-warning',
        'success' => 'bg-success/20 text-success',
        'danger' => 'bg-danger/20 text-danger',
        'waiting_deposit' => 'bg-warning/20 text-warning',
        'submitted' => 'bg-primary/15 text-primary',
        'verifying' => 'bg-primary/15 text-primary',
        'underpaid_waiting_customer' => 'bg-warning/20 text-warning',
        'overpaid_review' => 'bg-warning/20 text-warning',
        'expired' => 'bg-muted text-text-secondary',
        'cancelled' => 'bg-muted text-text-secondary',
    ];
    $key = strtolower((string) $status);
    $label = str_replace('_', ' ', $key);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' . ($map[$key] ?? $map['default'])]) }}>
    {{ $slot->isEmpty() ? ucwords($label) : $slot }}
</span>
