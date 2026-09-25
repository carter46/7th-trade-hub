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
    <x-dashboard.stat-grid :count="5" class="mb-6">
        <x-dashboard.stats-card
            label="Total sites"
            :value="number_format($statusCounts['total'])"
            :href="route('admin.websites')"
            icon="listings"
        />
        <x-dashboard.stats-card
            label="Active"
            :value="number_format($statusCounts['active'])"
            :href="route('admin.websites', ['status' => 'active'])"
            icon="listings"
        />
        <x-dashboard.stats-card
            label="Expired"
            :value="number_format($statusCounts['expired'])"
            :href="route('admin.websites', ['status' => 'expired'])"
            icon="listings"
        />
        <x-dashboard.stats-card
            label="Suspended"
            :value="number_format($statusCounts['suspended'])"
            :href="route('admin.websites', ['status' => 'suspended'])"
            icon="listings"
        />
        <x-dashboard.stats-card
            label="Other"
            :value="number_format($statusCounts['other'])"
            hint="Pending, cancelled, inactive"
            icon="listings"
        />
    </x-dashboard.stat-grid>

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
                        <x-dashboard.input name="q" type="text" :value="$filters['q'] ?? ''" placeholder="Search username, email, website…" />
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
            <x-dashboard.th>Dates</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th></x-dashboard.th>
        </x-slot:head>

        @foreach ($tools as $tool)
            @php
                $effectiveStatus = $tool->effectiveStatus();
                $displayExpiry = $tool->displayExpiresAt();
                $adminHold = $tool->wasEndedByAdminShutdown();
                $ownerName = $tool->user?->name;
            @endphp
            <tr>
                <x-dashboard.td>
                    @if ($tool->site_url)
                        <div class="truncate font-mono text-sm font-medium text-text-primary" title="{{ $tool->site_url }}">{{ \Illuminate\Support\Str::limit($tool->site_url, 48) }}</div>
                    @else
                        <div class="text-sm font-medium text-text-muted">No site URL yet</div>
                    @endif
                    @if ($tool->user && $ownerName)
                        <div class="mt-0.5">
                            <a
                                href="{{ route('admin.users.show', $tool->user) }}"
                                class="inline-block max-w-full truncate text-xs font-medium text-text-secondary underline-offset-2 hover:underline hover:text-text-primary"
                                title="{{ $ownerName }}"
                            >{{ $ownerName }}</a>
                        </div>
                    @else
                        <div class="mt-0.5 text-xs text-text-muted">—</div>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>
                    <div>
                        <span class="inline-block rounded bg-muted/50 px-1.5 py-0.5 font-mono text-xs font-semibold text-text-muted">Purchased {{ $tool->purchased_at?->format('j M Y') ?? '—' }}</span>
                    </div>
                    <div class="mt-0.5 font-mono text-xs text-text-muted">
                        @if (! $displayExpiry)
                            Expiry —
                        @elseif ($adminHold && $displayExpiry->isFuture())
                            Paid until {{ $displayExpiry->format('j M Y') }}
                        @elseif ($adminHold)
                            Was until {{ $displayExpiry->format('j M Y') }}
                        @elseif ($displayExpiry->isPast() || $effectiveStatus === \App\Enums\UserToolStatus::Expired)
                            Expired {{ $displayExpiry->format('j M Y') }}
                        @else
                            Expires {{ $displayExpiry->format('j M Y') }}
                        @endif
                    </div>
                </x-dashboard.td>
                <x-dashboard.td>
                    <div class="flex flex-col items-start gap-1">
                        <x-dashboard.badge :status="$effectiveStatus->value" class="!px-1.5 !py-0 !text-[10px] !leading-4 !font-medium" />
                        @if ($tool->isExpiringSoon())
                            <span class="text-[11px] leading-tight text-amber-600">Expiring soon</span>
                        @endif
                    </div>
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
