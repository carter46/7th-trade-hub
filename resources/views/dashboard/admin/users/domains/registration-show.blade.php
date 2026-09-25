@extends('layouts.dashboard-admin')

@section('title', 'Manage '.$registration->fqdn)

@section('content')
<x-layout.page
    :title="$registration->fqdn"
    width="default"
    :breadcrumb="[
        ['Users', route('admin.users')],
        [$user->name, route('admin.users.tools', $user)],
        ['Domain', null],
    ]"
>
    @if(session('status'))
        <x-dashboard.alert type="success">{{ session('status') }}</x-dashboard.alert>
    @endif
    @if(session('error'))
        <x-dashboard.alert type="danger">{{ session('error') }}</x-dashboard.alert>
    @endif

    <x-dashboard.card class="space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">Domain registration</p>
                <h2 class="mt-1 text-xl font-semibold text-text-primary font-mono break-all">{{ $registration->fqdn }}</h2>
            </div>
            <x-dashboard.badge :status="$registration->status" />
        </div>

        <dl class="grid gap-3 sm:grid-cols-2 text-sm">
            <div>
                <dt class="text-text-muted">Fulfillment</dt>
                <dd class="font-medium text-text-primary">{{ $registration->isManualFulfillment() ? 'Manual' : 'Provider' }}</dd>
            </div>
            <div>
                <dt class="text-text-muted">Provider key</dt>
                <dd class="font-medium text-text-primary">{{ $registration->provider_key ?: '—' }}</dd>
            </div>
            @if($registration->order)
                <div>
                    <dt class="text-text-muted">Order</dt>
                    <dd>
                        <a href="{{ route('admin.orders.show', $registration->order) }}" class="font-medium text-primary hover:underline">
                            {{ $registration->order->reference }}
                        </a>
                    </dd>
                </div>
            @endif
            <div>
                <dt class="text-text-muted">Registered at</dt>
                <dd class="font-medium text-text-primary">{{ $registration->registered_at?->format('j M Y H:i') ?? '—' }}</dd>
            </div>
            @if($registration->provider_reference)
                <div>
                    <dt class="text-text-muted">Registrar reference</dt>
                    <dd class="font-medium text-text-primary font-mono">{{ $registration->provider_reference }}</dd>
                </div>
            @endif
        </dl>

        @if($registration->unavailableFqdn())
            <div class="rounded-lg border border-amber-500/30 bg-amber-500/5 px-4 py-3 text-sm">
                <p class="font-semibold text-amber-700 dark:text-amber-300">Closely related domain used</p>
                <p class="mt-1 text-text-secondary">Requested <span class="font-mono">{{ $registration->unavailableFqdn() }}</span> was unavailable.</p>
                <p class="mt-1 text-text-primary">Registered: <span class="font-mono font-semibold">{{ $registration->fqdn }}</span></p>
            </div>
        @endif

        @if($registration->rejectedFqdn())
            <div class="rounded-lg border border-danger/30 bg-danger/5 px-4 py-3 text-sm">
                <p class="font-semibold text-danger">Rejected domain</p>
                <p class="mt-1 font-mono text-text-primary">{{ $registration->rejectedFqdn() }}</p>
                @if($registration->rejectionReason())
                    <p class="mt-2 text-text-secondary">Reason: {{ $registration->rejectionReason() }}</p>
                @endif
            </div>
        @elseif($registration->isRejected() && $registration->error_message)
            <x-dashboard.alert type="danger">{{ $registration->error_message }}</x-dashboard.alert>
        @endif

        @if($registration->isPendingReplacement())
            <x-dashboard.alert type="info">
                Customer requested replacement domain <span class="font-mono font-semibold">{{ $registration->fqdn }}</span>. Approve after offline registration, or reject with a reason.
            </x-dashboard.alert>
        @endif

        @if(is_array($registration->registrant_contact))
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-text-muted mb-2">Registrant</p>
                <dl class="grid gap-1 sm:grid-cols-2 text-xs">
                    <div><span class="text-text-muted">Name:</span> {{ trim(($registration->registrant_contact['first_name'] ?? $registration->registrant_contact['firstName'] ?? '').' '.($registration->registrant_contact['last_name'] ?? $registration->registrant_contact['lastName'] ?? '')) }}</div>
                    <div><span class="text-text-muted">Email:</span> {{ $registration->registrant_contact['email'] ?? '—' }}</div>
                    <div><span class="text-text-muted">Phone:</span> {{ $registration->registrant_contact['phone'] ?? '—' }}</div>
                    <div><span class="text-text-muted">Country:</span> {{ $registration->registrant_contact['country'] ?? '—' }}</div>
                </dl>
            </div>
        @endif

        @if($registration->nameserverList() !== [])
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-text-muted mb-2">Nameservers</p>
                <ul class="text-sm space-y-1">
                    @foreach($registration->nameserverList() as $ns)
                        <li class="font-mono text-text-primary">{{ $ns }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </x-dashboard.card>

    <x-dashboard.card class="mt-6 space-y-4" x-data="{ open: {{ (old('fqdn') !== null || $errors->has('fqdn')) ? 'true' : 'false' }} }">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-text-primary">Replace domain</h3>
                <p class="mt-1 text-xs text-text-muted">
                    Change the FQDN whether this domain is pending or already registered. Updates order lines, tool Website URLs, and domain connections. If a website was already integrated, re-install credentials on the new host and run Check connection.
                </p>
            </div>
            <x-dashboard.button type="button" variant="secondary" size="sm" x-on:click="open = !open" x-text="open ? 'Hide' : 'Replace domain'">
                Replace domain
            </x-dashboard.button>
        </div>
        <form
            method="POST"
            action="{{ route('admin.users.domains.registrations.replace', [$user, $registration]) }}"
            class="space-y-3 border-t border-border-subtle pt-4"
            x-show="open"
            x-cloak
        >
            @csrf
            <x-dashboard.input
                name="fqdn"
                label="New domain"
                :value="old('fqdn')"
                placeholder="example.com"
                required
                hint="Apex domain only (e.g. example.com)."
            />
            @error('fqdn')
                <p class="text-xs text-danger">{{ $message }}</p>
            @enderror
            <div>
                <label for="replace_note" class="mb-1 block text-xs text-text-muted">Internal note (optional)</label>
                <textarea id="replace_note" name="note" rows="2" maxlength="500" class="w-full rounded-lg border border-border-default bg-elevated px-3 py-2 text-sm" placeholder="Why this domain was changed…">{{ old('note') }}</textarea>
            </div>
            <x-dashboard.button type="submit" variant="primary">Save new domain</x-dashboard.button>
        </form>
    </x-dashboard.card>

    @if($registration->isManualFulfillment() && $registration->awaitsManualAdminAction())
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <x-dashboard.card class="space-y-4">
                <h3 class="text-sm font-semibold text-text-primary">Approve domain</h3>
                <p class="text-xs text-text-muted">Confirm you have registered this domain offline, then approve. If the requested name was taken, enter the closely related domain you registered instead — it replaces the FQDN everywhere (order, tools, Website URL setup).</p>
                <form method="POST" action="{{ route('admin.users.domains.registrations.approve', [$user, $registration]) }}" class="space-y-3">
                    @csrf
                    <x-dashboard.input name="provider_reference" label="Registrar reference (optional)" :value="old('provider_reference')" hint="Included in the customer confirmation email when provided." />
                    <x-dashboard.input
                        name="closely_related_fqdn"
                        label="Closely related domain (if requested name unavailable)"
                        :value="old('closely_related_fqdn')"
                        placeholder="example-alt.com"
                        hint="Leave blank if you registered the exact requested domain. When set, this FQDN becomes the registered domain and updates Website URL / tools."
                    />
                    <div>
                        <label class="mb-1 block text-xs text-text-muted">Nameservers (optional, comma-separated)</label>
                        <input type="text" name="nameservers" value="{{ old('nameservers') }}" class="w-full rounded-lg border border-border-default bg-elevated px-3 py-2 text-sm" placeholder="ns1.example.com, ns2.example.com">
                    </div>
                    <x-dashboard.button type="submit" variant="primary">Approve &amp; mark registered</x-dashboard.button>
                </form>
            </x-dashboard.card>

            <x-dashboard.card class="space-y-4">
                <h3 class="text-sm font-semibold text-text-primary">Reject domain</h3>
                <p class="text-xs text-text-muted">Customer will be emailed and can submit a free replacement FQDN.</p>
                <form method="POST" action="{{ route('admin.users.domains.registrations.reject', [$user, $registration]) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label for="reason" class="mb-1 block text-sm font-medium text-text-primary">Reason <span class="text-danger">*</span></label>
                        <textarea id="reason" name="reason" rows="4" required minlength="5" maxlength="500" class="w-full rounded-lg border border-border-default bg-elevated px-3 py-2 text-sm" placeholder="Explain why this domain cannot be registered…">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-dashboard.button type="submit" variant="danger">Reject domain</x-dashboard.button>
                </form>
            </x-dashboard.card>
        </div>
    @elseif(! $registration->isManualFulfillment())
        <x-dashboard.card class="mt-6">
            <p class="text-sm text-text-secondary">
                This domain uses <strong>provider fulfillment</strong>. Manual approve/reject does not apply, but you can still use <strong>Replace domain</strong> above to change the FQDN across orders and tools.
            </p>
        </x-dashboard.card>
    @elseif($registration->isRejected())
        <x-dashboard.card class="mt-6">
            <p class="text-sm text-text-secondary">Waiting for the customer to submit a free replacement domain — or use <strong>Replace domain</strong> above to set one yourself.</p>
        </x-dashboard.card>
    @endif

    @if($registration->replacementHistory() !== [])
        <x-dashboard.card class="mt-6 space-y-3">
            <h3 class="text-sm font-semibold text-text-primary">Rejection history</h3>
            <ul class="space-y-2 text-sm">
                @foreach(array_reverse($registration->replacementHistory()) as $row)
                    <li class="rounded-lg border border-border-subtle px-3 py-2">
                        <span class="font-mono font-medium">{{ $row['fqdn'] ?? '—' }}</span>
                        <span class="text-text-muted"> · {{ $row['rejected_at'] ?? '' }}</span>
                        <p class="mt-1 text-text-secondary">{{ $row['reason'] ?? '' }}</p>
                    </li>
                @endforeach
            </ul>
        </x-dashboard.card>
    @endif
</x-layout.page>
@endsection
