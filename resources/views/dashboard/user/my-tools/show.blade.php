@extends('layouts.dashboard-user')

@section('title', $tool->resolvedDisplayName())

@section('content')
@php
    use App\Enums\UserToolStatus;
    $isPending = $tool->status === UserToolStatus::PendingSetup;
    $isExpired = $tool->status === UserToolStatus::Expired;
    $isActive = $tool->status === UserToolStatus::Active;
@endphp
<x-layout.page
    title="{{ $tool->resolvedDisplayName() }}"
    subtitle="{{ $tool->product?->title }}"
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

    @if ($isPending)
        <x-dashboard.card>
            <h2 class="text-lg font-semibold text-text-primary">Pending setup</h2>
            <p class="mt-2 text-sm text-text-secondary">
                Payment received. Our team is configuring your website. You will see URLs and login controls here once setup is complete.
            </p>
            <dl class="mt-6 grid gap-3 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-text-muted">Plan</dt>
                    <dd class="font-medium">{{ $tool->variant?->displayLabel() ?? ($tool->duration_months ? $tool->duration_months.' months' : '—') }}</dd>
                </div>
                <div>
                    <dt class="text-text-muted">Purchased</dt>
                    <dd class="font-medium">{{ $tool->purchased_at?->format('j M Y') ?? '—' }}</dd>
                </div>
            </dl>
        </x-dashboard.card>
    @else
        <div class="grid gap-6 lg:grid-cols-2">
            <x-dashboard.card class="space-y-4">
                <h2 class="text-lg font-semibold text-text-primary">Access</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-text-muted">Site URL</dt>
                        <dd>
                            @if ($tool->site_url)
                                <a href="{{ $tool->site_url }}" class="text-primary underline" target="_blank" rel="noopener">{{ $tool->site_url }}</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">Admin login URL</dt>
                        <dd class="break-all">{{ $tool->admin_login_url ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">Admin email</dt>
                        <dd>{{ $tool->admin_email ?? '—' }}</dd>
                    </div>
                </dl>
                <div class="flex flex-wrap gap-2 pt-2">
                    @if ($tool->admin_password)
                        <x-dashboard.button type="button" variant="secondary" size="sm" id="copy-tool-password" data-url="{{ route('dashboard.my-tools.password', $tool) }}">
                            Copy password
                        </x-dashboard.button>
                    @endif
                    @if ($tool->canLaunchAdmin())
                        <form method="POST" action="{{ route('dashboard.my-tools.launch-admin', $tool) }}">
                            @csrf
                            <x-dashboard.button type="submit" size="sm">Login as admin</x-dashboard.button>
                        </form>
                    @endif
                </div>
                <p class="text-xs text-text-muted">Password is never shown on this page. Login as admin creates a session on your site automatically.</p>
            </x-dashboard.card>

            <x-dashboard.card class="space-y-3">
                <h2 class="text-lg font-semibold text-text-primary">Subscription</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-text-muted">Status</dt>
                        <dd>
                            <x-dashboard.badge :status="$tool->status->value" />
                            @if ($tool->isExpiringSoon())
                                <span class="ml-1 text-amber-600">Expiring soon</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">Plan</dt>
                        <dd>{{ $tool->variant?->displayLabel() ?? ($tool->duration_months ? $tool->duration_months.' months' : '—') }}</dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">Purchased</dt>
                        <dd>{{ $tool->purchased_at?->format('j M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">Expires</dt>
                        <dd>{{ $tool->expires_at?->format('j M Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </x-dashboard.card>
        </div>
    @endif
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
