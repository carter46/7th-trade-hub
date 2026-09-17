@extends('layouts.dashboard-admin')

@section('title', 'Websites')

@section('content')
<x-layout.page
    title="Websites"
    subtitle="All purchased website packages across members."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Operations', null],
        ['Websites', null],
    ]"
>
    <x-dashboard.table
        :empty="$tools->isEmpty()"
        empty-title="No purchased websites"
        empty-description="When members buy a website package, it will appear here."
        empty-icon="listings"
        striped
    >
        <x-slot:filters>
            <x-dashboard.filter-bar>
                <form method="GET" class="contents">
                    <div class="min-w-[10rem] flex-1">
                        <x-dashboard.input name="q" type="text" :value="$filters['q'] ?? ''" placeholder="Search owner, email, website…" />
                    </div>
                    <div class="min-w-[9rem]">
                        <x-dashboard.select name="status">
                            <option value="">All statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </x-dashboard.select>
                    </div>
                    <x-dashboard.button type="submit" variant="secondary">Filter</x-dashboard.button>
                </form>
            </x-dashboard.filter-bar>
        </x-slot:filters>

        <x-slot:head>
            <x-dashboard.th>Website</x-dashboard.th>
            <x-dashboard.th>Owner</x-dashboard.th>
            <x-dashboard.th>Email</x-dashboard.th>
            <x-dashboard.th>Purchased</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Expiry</x-dashboard.th>
            <x-dashboard.th></x-dashboard.th>
        </x-slot:head>

        @foreach ($tools as $tool)
            @php
                $effectiveStatus = $tool->effectiveStatus();
                $expiryPast = $tool->expires_at && $tool->expires_at->isPast();
            @endphp
            <tr>
                <x-dashboard.td>
                    <div class="font-medium text-text-primary">{{ $tool->resolvedDisplayName() }}</div>
                    @if ($tool->site_url)
                        <div class="mt-0.5 truncate font-mono text-xs text-text-muted" title="{{ $tool->site_url }}">{{ \Illuminate\Support\Str::limit($tool->site_url, 40) }}</div>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td class="font-medium text-text-primary">
                    @if ($tool->user)
                        <a href="{{ route('admin.users.show', $tool->user) }}" class="underline-offset-2 hover:underline">{{ $tool->user->name }}</a>
                    @else
                        —
                    @endif
                </x-dashboard.td>
                <x-dashboard.td class="text-sm text-text-secondary">{{ $tool->user?->email ?? '—' }}</x-dashboard.td>
                <x-dashboard.td class="text-xs text-text-muted">{{ $tool->purchased_at?->format('j M Y') ?? '—' }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$effectiveStatus->value" />
                    @if ($tool->isExpiringSoon())
                        <span class="mt-1 block text-xs text-amber-600">Expiring soon</span>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td class="text-xs text-text-muted">
                    @if (! $tool->expires_at)
                        —
                    @elseif ($expiryPast || $effectiveStatus === \App\Enums\UserToolStatus::Expired)
                        Expired {{ $tool->expires_at->format('j M Y') }}
                    @else
                        Expires {{ $tool->expires_at->format('j M Y') }}
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>
                    @if ($tool->user)
                        <x-dashboard.button
                            :href="route('admin.users.tools.show', [$tool->user, $tool])"
                            variant="secondary"
                            size="xs"
                        >
                            View
                        </x-dashboard.button>
                    @endif
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$tools" />
    </x-slot:pagination>
</x-layout.page>
@endsection
