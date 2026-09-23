@extends('layouts.dashboard-admin')

@section('title', 'Order '.$order->reference)

@section('content')
@php
    $meta = $order->payment_metadata ?? [];
    $hasProof = ! empty($meta['proof_path'] ?? null);
    $awaitingBank = $order->isAwaitingManualBankTransfer();
    $proofInlineUrl = $hasProof ? route('admin.orders.proof', ['order' => $order, 'inline' => 1]) : null;
    $proofDownloadUrl = $hasProof ? route('admin.orders.proof', $order) : null;
@endphp
<x-layout.page
    :title="'Order '.$order->reference"
    subtitle="Platform service purchase"
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Orders', route('admin.orders')],
        [$order->reference, null],
    ]"
>
    @if (session('status'))
        <x-dashboard.alert type="success" class="mb-4">{{ session('status') }}</x-dashboard.alert>
    @endif
    @if (session('error'))
        <x-dashboard.alert type="danger" class="mb-4">{{ session('error') }}</x-dashboard.alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <x-dashboard.card class="space-y-3 text-sm">
            <h2 class="text-lg font-semibold text-text-primary">Order</h2>
            <dl class="space-y-2">
                <div class="flex justify-between gap-4"><dt class="text-text-muted">User</dt><dd>{{ \App\Models\User::labelFor($order->user) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-text-muted">Amount</dt><dd class="font-semibold">₦{{ number_format((float) $order->total_amount, 2) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-text-muted">Payment</dt><dd>{{ str_replace('_', ' ', $order->payment_method ?? '—') }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-text-muted">Status</dt><dd><x-dashboard.badge :status="$order->status" /></dd></div>
                @if ($order->payment_submitted_at)
                    <div class="flex justify-between gap-4"><dt class="text-text-muted">Proof submitted</dt><dd>{{ $order->payment_submitted_at->format('M j, Y g:i A') }}</dd></div>
                @endif
                @if ($order->payment_confirmed_at)
                    <div class="flex justify-between gap-4"><dt class="text-text-muted">Confirmed</dt><dd>{{ $order->payment_confirmed_at->format('M j, Y g:i A') }}</dd></div>
                @endif
                @if ($order->payment_method === \App\Models\Order::PAYMENT_MANUAL_BANK_TRANSFER)
                    @php
                        $paymentSession = (int) ($meta['manual_payment_session'] ?? 1);
                        $maxSessions = \App\Modules\Catalog\Services\PlatformCheckoutService::MANUAL_PAYMENT_MAX_SESSIONS;
                    @endphp
                    <div class="flex justify-between gap-4"><dt class="text-text-muted">Payment attempts</dt><dd>{{ $paymentSession }} / {{ $maxSessions }}</dd></div>
                    @if (! empty($meta['manual_payment_failed_at']))
                        <div class="flex justify-between gap-4"><dt class="text-text-muted">Last window expired</dt><dd>{{ \Illuminate\Support\Carbon::parse($meta['manual_payment_failed_at'])->format('M j, Y g:i A') }}</dd></div>
                    @endif
                    @if (! empty($meta['manual_payment_cancelled_at']))
                        <div class="flex justify-between gap-4"><dt class="text-text-muted">Cancelled at</dt><dd>{{ \Illuminate\Support\Carbon::parse($meta['manual_payment_cancelled_at'])->format('M j, Y g:i A') }}</dd></div>
                    @endif
                    @if (! empty($meta['cancel_reason']))
                        <div class="flex justify-between gap-4"><dt class="text-text-muted">Cancel reason</dt><dd class="text-right">{{ $meta['cancel_reason'] }}</dd></div>
                    @endif
                @endif
                @if ($order->paymentConfirmer)
                    <div class="flex justify-between gap-4"><dt class="text-text-muted">Confirmed by</dt><dd>{{ $order->paymentConfirmer->name }}</dd></div>
                @endif
            </dl>

            @if ($awaitingBank)
                <div class="flex flex-wrap gap-2 pt-2">
                    <form method="POST" action="{{ route('admin.orders.confirm', $order) }}">
                        @csrf
                        <x-dashboard.button type="submit" size="sm" variant="primary">Confirm payment</x-dashboard.button>
                    </form>
                    <form method="POST" action="{{ route('admin.orders.reject', $order) }}" class="space-y-2" onsubmit="return confirm('Cancel this order and release any domain holds?');">
                        @csrf
                        <textarea name="notes" rows="2" placeholder="Reason (optional)" class="w-full rounded-lg border border-border-subtle bg-surface px-3 py-2 text-sm">{{ old('notes') }}</textarea>
                        <x-dashboard.button type="submit" size="sm" variant="danger">Reject / cancel</x-dashboard.button>
                    </form>
                </div>
            @endif
        </x-dashboard.card>

        <x-dashboard.card class="space-y-3 text-sm" x-data="{ proofOpen: false }">
            <h2 class="text-lg font-semibold text-text-primary">Items</h2>
            <ul class="space-y-3">
                @foreach ($order->items as $item)
                    @php
                        $opts = $item->options ?? [];
                        $title = $opts['product_title'] ?? ($item->variant?->product?->title ?? null);
                        $variantLabel = $opts['variant_label'] ?? $item->variant?->displayLabel();
                        $domainMode = $opts['domain_mode'] ?? null;
                        $fqdn = $opts['domain_fqdn'] ?? $opts['domain_name'] ?? null;
                        $tld = $opts['domain_tld'] ?? $opts['tld'] ?? null;
                        $fulfillment = $opts['domain_fulfillment'] ?? null;
                    @endphp
                    <li class="rounded-xl border border-border-subtle px-3 py-3 space-y-1.5">
                        <div class="flex justify-between gap-4">
                            <div>
                                <p class="font-medium text-text-primary">
                                    {{ $title ?: ($variantLabel ?: 'Product #'.$item->item_id) }}
                                </p>
                                @if ($variantLabel && $title)
                                    <p class="text-xs text-text-muted">Plan: {{ $variantLabel }} × {{ $item->quantity }}</p>
                                @elseif (! $title)
                                    <p class="text-xs text-text-muted">{{ $variantLabel ?? 'Item' }} × {{ $item->quantity }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 font-semibold">₦{{ number_format((float) $item->line_total, 2) }}</span>
                        </div>

                        @if ($domainMode === 'buy' && filled($fqdn))
                            <p class="text-xs text-text-secondary">
                                Domain purchase: <span class="font-medium text-text-primary">{{ $fqdn }}</span>
                                @if (filled($tld))
                                    <span class="text-text-muted">({{ str_starts_with((string) $tld, '.') ? $tld : '.'.$tld }})</span>
                                @endif
                                @if ($fulfillment === 'manual')
                                    <span class="text-amber-700">· manual fulfillment</span>
                                @endif
                            </p>
                        @elseif ($domainMode === 'connect' && filled($fqdn))
                            <p class="text-xs text-text-secondary">
                                Connect existing domain: <span class="font-medium text-text-primary">{{ $fqdn }}</span>
                                <span class="text-text-muted">· no domain charge</span>
                            </p>
                        @elseif ($domainMode === 'buy')
                            <p class="text-xs text-amber-700">Domain buy selected (details missing on line)</p>
                        @endif
                    </li>
                @endforeach
            </ul>

            @if ($order->payment_method === \App\Models\Order::PAYMENT_MANUAL_BANK_TRANSFER)
                <h3 class="text-sm font-semibold text-text-primary pt-2">Bank details (company)</h3>
                <dl class="space-y-1">
                    <div class="flex justify-between gap-4"><dt class="text-text-muted">Bank</dt><dd>{{ $bankDetails['bank_name'] ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-text-muted">Account</dt><dd class="font-mono">{{ $bankDetails['account_number'] ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-text-muted">Name</dt><dd>{{ $bankDetails['account_name'] ?: '—' }}</dd></div>
                </dl>

                @if (! empty($meta['payer_bank_name']) || ! empty($meta['transfer_reference']))
                    <h3 class="text-sm font-semibold text-text-primary pt-2">Customer transfer</h3>
                    <dl class="space-y-1">
                        @if (! empty($meta['payer_bank_name']))
                            <div class="flex justify-between gap-4"><dt class="text-text-muted">Payer bank</dt><dd>{{ $meta['payer_bank_name'] }}</dd></div>
                        @endif
                        @if (! empty($meta['transfer_reference']))
                            <div class="flex justify-between gap-4"><dt class="text-text-muted">Reference</dt><dd>{{ $meta['transfer_reference'] }}</dd></div>
                        @endif
                    </dl>
                @endif

                @if ($hasProof)
                    <div class="flex flex-wrap gap-2 pt-2">
                        <x-dashboard.button type="button" variant="secondary" size="sm" x-on:click="proofOpen = true">View proof</x-dashboard.button>
                        <x-dashboard.button :href="$proofDownloadUrl" variant="ghost" size="sm">Download</x-dashboard.button>
                    </div>

                    <div
                        x-show="proofOpen"
                        x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
                        role="dialog"
                        aria-modal="true"
                        x-on:keydown.escape.window="proofOpen = false"
                    >
                        <div class="relative w-full max-w-3xl rounded-2xl border border-border-default bg-surface p-4 shadow-xl space-y-3" x-on:click.stop>
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-base font-semibold text-text-primary">Payment proof</h3>
                                <button type="button" class="text-sm text-text-muted hover:text-text-primary" x-on:click="proofOpen = false">Close</button>
                            </div>
                            @if ($proofIsImage ?? false)
                                <img src="{{ $proofInlineUrl }}" alt="Payment proof" class="max-h-[70vh] w-full rounded-lg object-contain bg-muted/30">
                            @elseif ($proofIsPdf ?? false)
                                <iframe src="{{ $proofInlineUrl }}" title="Payment proof PDF" class="h-[70vh] w-full rounded-lg border border-border-subtle"></iframe>
                            @else
                                <p class="text-sm text-text-secondary">Preview is not available for this file type.</p>
                                <x-dashboard.button :href="$proofDownloadUrl" size="sm">Download proof</x-dashboard.button>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </x-dashboard.card>
    </div>

    @if (($pendingManualDomains ?? collect())->isNotEmpty())
        <x-dashboard.card class="mt-6 space-y-4 text-sm">
            <h2 class="text-lg font-semibold text-text-primary">Manual domain fulfillment</h2>
            <p class="text-xs text-text-muted">These domains were purchased while registrar providers were offline. Register them offline, then mark them registered here.</p>
            @foreach ($pendingManualDomains as $registration)
                <div class="rounded-xl border border-border-subtle px-4 py-3 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold text-text-primary">{{ $registration->fqdn }}</p>
                        <x-dashboard.badge status="pending_manual" />
                    </div>
                    @if (is_array($registration->registrant_contact))
                        <dl class="grid gap-1 sm:grid-cols-2 text-xs">
                            <div><span class="text-text-muted">Name:</span> {{ trim(($registration->registrant_contact['first_name'] ?? $registration->registrant_contact['firstName'] ?? '').' '.($registration->registrant_contact['last_name'] ?? $registration->registrant_contact['lastName'] ?? '')) }}</div>
                            <div><span class="text-text-muted">Email:</span> {{ $registration->registrant_contact['email'] ?? '—' }}</div>
                            <div><span class="text-text-muted">Phone:</span> {{ $registration->registrant_contact['phone'] ?? '—' }}</div>
                            <div><span class="text-text-muted">Country:</span> {{ $registration->registrant_contact['country'] ?? '—' }}</div>
                        </dl>
                    @endif
                    <form method="POST" action="{{ route('admin.orders.domains.mark-registered', [$order, $registration]) }}" class="grid gap-2 sm:grid-cols-2">
                        @csrf
                        <x-dashboard.input name="provider_reference" label="Registrar reference (optional)" :value="old('provider_reference')" />
                        <div>
                            <label class="mb-1 block text-xs text-text-muted">Nameservers (optional, comma-separated)</label>
                            <input type="text" name="nameservers" value="{{ old('nameservers') }}" class="w-full rounded-lg border border-border-default bg-elevated px-3 py-2 text-sm" placeholder="ns1.example.com, ns2.example.com">
                        </div>
                        <div class="sm:col-span-2">
                            <x-dashboard.button type="submit" size="sm" variant="primary">Mark registered</x-dashboard.button>
                        </div>
                    </form>
                </div>
            @endforeach
        </x-dashboard.card>
    @endif
</x-layout.page>
@endsection
