@extends('layouts.dashboard-user')

@section('title', $tool->resolvedDisplayName())

@section('content')
@php
    use App\Enums\UserToolStatus;

    $product = $tool->product;
    $isPending = $tool->status === UserToolStatus::PendingSetup;
    $isExpired = $tool->status === UserToolStatus::Expired;
    $isActive = $tool->status === UserToolStatus::Active;
    $heroUrl = $product
        ? (media_url($product->heroMedia, $product->hero_image, 'large')
            ?? media_url($product->heroMedia, $product->hero_image, 'medium'))
        : null;
    $paidAmount = $tool->orderItem?->line_total
        ?? $tool->order?->total_amount
        ?? $tool->variant?->price;
    $planLabel = $tool->variant?->displayLabel() ?? ($tool->duration_months ? $tool->duration_months.' months' : '—');
    $pendingLabel = 'Pending';
@endphp
<x-layout.page
    title="{{ $tool->resolvedDisplayName() }}"
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['My Tools', route('dashboard.my-tools')],
        [$tool->resolvedDisplayName(), null],
    ]"
>
    <x-slot:actions>
        @if ($isActive && $tool->isExpiringSoon())
            <x-dashboard.button :href="route('dashboard.services.checkout', ['slug' => $tool->product->slug, 'renew' => $tool->public_id])" size="sm">Renew</x-dashboard.button>
        @elseif ($isExpired && $tool->product)
            <x-dashboard.button :href="route('dashboard.services.checkout', ['slug' => $tool->product->slug, 'renew' => $tool->public_id])" size="sm">Renew</x-dashboard.button>
        @endif
    </x-slot:actions>

    <div class="space-y-6">
        <x-dashboard.card>
            <div class="grid gap-4 lg:grid-cols-[minmax(0,240px)_1fr] lg:gap-6">
                <div class="rounded-xl border border-border-subtle bg-muted/40 p-3 sm:p-4">
                    <div class="relative aspect-[4/3] overflow-hidden rounded-lg bg-muted lg:min-h-[200px]">
                        @if ($heroUrl)
                            <img
                                src="{{ $heroUrl }}"
                                alt="{{ $product?->title ?? $tool->resolvedDisplayName() }}"
                                class="absolute inset-0 h-full w-full object-cover"
                            >
                        @else
                            <div class="flex h-full min-h-[180px] items-center justify-center bg-gradient-to-br from-primary/20 via-muted to-elevated">
                                <x-ui.icon name="monitor" class="h-12 w-12 text-primary/50" />
                            </div>
                        @endif
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Your service</p>
                        <h2 class="mt-1 text-2xl font-bold text-text-primary">{{ $product?->title ?? $tool->resolvedDisplayName() }}</h2>
                        @if (filled($product?->short_description))
                            <p class="mt-2 text-sm text-text-secondary">{{ $product->short_description }}</p>
                        @endif
                    </div>
                    <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">
                        <div>
                            <dt class="text-text-muted">Plan</dt>
                            <dd class="font-medium text-text-primary">{{ $planLabel }}</dd>
                        </div>
                        <div>
                            <dt class="text-text-muted">Amount paid</dt>
                            <dd class="font-medium text-text-primary">
                                @if ($paidAmount !== null)
                                    ₦{{ number_format((float) $paidAmount, 2) }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-text-muted">Purchased</dt>
                            <dd class="font-medium text-text-primary">{{ $tool->purchased_at?->format('j M Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-text-muted">Status</dt>
                            <dd><x-dashboard.badge :status="$tool->status->value" /></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </x-dashboard.card>

        @if ($isPending)
            <p class="rounded-xl border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-text-secondary">
                Our team is configuring your service. Your admin login details will appear once done.
            </p>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <x-dashboard.card class="space-y-4">
                <h2 class="text-lg font-semibold text-text-primary">Admin access</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-text-muted">Site URL</dt>
                        <dd class="font-medium text-text-primary">
                            @if ($tool->site_url)
                                <a href="{{ $tool->site_url }}" class="text-primary underline break-all" target="_blank" rel="noopener">{{ $tool->site_url }}</a>
                            @else
                                <span class="text-text-muted">{{ $pendingLabel }}</span>
                            @endif
                        </dd>
                    </div>
                    @if ($tool->admin_login_url)
                        <div>
                            <dt class="text-text-muted">Admin login URL</dt>
                            <dd class="break-all font-medium text-text-primary">{{ $tool->admin_login_url }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-text-muted">Admin login email</dt>
                        <dd class="font-medium text-text-primary">
                            @if ($tool->admin_email)
                                {{ $tool->admin_email }}
                            @else
                                <span class="text-text-muted">{{ $pendingLabel }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">Password</dt>
                        <dd class="font-medium text-text-primary">
                            @if ($tool->admin_password)
                                <span class="text-text-secondary">Saved securely — use Copy password below</span>
                            @else
                                <span class="text-text-muted">{{ $pendingLabel }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                @if (! $isPending)
                    <div class="flex flex-wrap gap-2 pt-2">
                        @if ($tool->admin_password)
                            <x-dashboard.button type="button" variant="secondary" size="sm" id="copy-tool-password" data-url="{{ route('dashboard.my-tools.password', $tool) }}">
                                Copy password
                            </x-dashboard.button>
                        @endif
                        @if ($tool->canLaunchAdmin())
                            <form method="POST" action="{{ route('dashboard.my-tools.launch-admin', $tool) }}" target="_blank" rel="noopener">
                                @csrf
                                <x-dashboard.button type="submit" size="sm">Login as admin</x-dashboard.button>
                            </form>
                        @endif
                    </div>
                    <p class="text-xs text-text-muted">Password is never shown on this page. Login as admin opens your site in a new tab.</p>
                @endif
            </x-dashboard.card>

            <x-dashboard.card class="space-y-3">
                <h2 class="text-lg font-semibold text-text-primary">Subscription</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-text-muted">Plan</dt>
                        <dd class="font-medium text-text-primary">{{ $planLabel }}</dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">Purchased</dt>
                        <dd class="font-medium text-text-primary">{{ $tool->purchased_at?->format('j M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">Expires</dt>
                        <dd class="font-medium text-text-primary">
                            @if ($tool->expires_at)
                                {{ $tool->expires_at->format('j M Y') }}
                                @if ($tool->isExpiringSoon())
                                    <span class="ml-1 text-amber-600">Expiring soon</span>
                                @endif
                            @elseif ($isPending)
                                <span class="text-text-muted">Starts after setup</span>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    @if ($tool->order?->reference)
                        <div>
                            <dt class="text-text-muted">Order reference</dt>
                            <dd class="font-mono text-xs text-text-primary">{{ $tool->order->reference }}</dd>
                        </div>
                    @endif
                </dl>
            </x-dashboard.card>
        </div>
    </div>
</x-layout.page>

@push('scripts')
<script>
(() => {
  const btn = document.getElementById('copy-tool-password');
  if (!btn) return;
  btn.addEventListener('click', async () => {
    const url = btn.getAttribute('data-url');
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': token || '',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Unable to copy');
      await navigator.clipboard.writeText(data.password);
      btn.textContent = 'Copied';
      setTimeout(() => { btn.textContent = 'Copy password'; }, 2000);
    } catch (e) {
      alert(e.message || 'Copy failed');
    }
  });
})();
</script>
@endpush
@endsection
