@extends('layouts.dashboard-user')

@section('title', 'My Listings')

@section('content')
<x-layout.page
    title="My Listings"
    subtitle="Create, submit, and track your marketplace listings."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Marketplace', route('dashboard.marketplace')],
        ['My Listings', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('dashboard.listings.create')" icon="plus">Create Listing</x-dashboard.button>
    </x-slot:actions>

    @if ($listings->isEmpty())
        <div class="overflow-hidden rounded-2xl border border-border-default bg-elevated shadow-panel">
            <x-dashboard.empty-state
                icon="listings"
                title="You have no listings yet"
                description="Create a draft listing, then submit it for admin review."
                :action="['href' => route('dashboard.listings.create'), 'label' => 'Create your first listing']"
            />
        </div>
    @else
        {{-- Mobile: accordion --}}
        <div class="space-y-3 md:hidden">
            @foreach ($listings as $listing)
                @php
                    $badgeStatus = match ($listing->status) {
                        'published' => 'completed',
                        'pending_review' => 'pending',
                        'rejected' => 'rejected',
                        'draft' => 'neutral',
                        'archived' => 'neutral',
                        'suspended' => 'danger',
                        default => 'default',
                    };
                    $hasActions = $listing->status !== 'pending_review';
                @endphp
                <div
                    class="overflow-hidden rounded-2xl border border-border-default bg-elevated shadow-panel"
                    @if ($hasActions) x-data="{ open: false }" @endif
                >
                    <div class="flex w-full items-start gap-3 p-4">
                        <div class="min-w-0 flex-1">
                            @if ($listing->status === 'published')
                                <a
                                    href="{{ route('dashboard.marketplace.show', $listing->slug) }}"
                                    class="block text-base font-medium text-primary break-words hover:underline"
                                >{{ $listing->title }}</a>
                            @else
                                <div class="text-base font-medium text-text-primary break-words">{{ $listing->title }}</div>
                            @endif
                            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-sm">
                                <span class="font-medium text-text-primary">₦{{ number_format($listing->price, 2) }}</span>
                                <x-dashboard.badge :status="$badgeStatus">{{ str_replace('_', ' ', $listing->status) }}</x-dashboard.badge>
                            </div>
                            <div class="mt-1.5 text-xs text-text-muted">
                                Updated {{ $listing->updated_at->format('M j, Y') }}
                                @unless ($hasActions)
                                    · Awaiting admin
                                @endunless
                            </div>
                        </div>
                        @if ($hasActions)
                            <button
                                type="button"
                                class="mt-0.5 inline-flex size-9 shrink-0 items-center justify-center rounded-lg text-text-secondary hover:bg-muted/60 hover:text-text-primary focus-ring"
                                @click="open = !open"
                                :aria-expanded="open.toString()"
                                aria-label="Toggle actions"
                            >
                                <x-ui.icon
                                    name="chevron-down"
                                    class="h-5 w-5 transition-transform"
                                    x-bind:class="open && 'rotate-180'"
                                />
                            </button>
                        @endif
                    </div>

                    @if ($hasActions)
                        <div
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="border-t border-border-default px-4 py-3"
                        >
                            @include('dashboard.user.listings._actions', ['listing' => $listing, 'mode' => 'buttons'])
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Desktop: table --}}
        <div class="hidden md:block">
            <x-dashboard.table striped :min-height="false">
                <x-slot:head>
                    <x-dashboard.th>Title</x-dashboard.th>
                    <x-dashboard.th>Price</x-dashboard.th>
                    <x-dashboard.th>Status</x-dashboard.th>
                    <x-dashboard.th>Actions</x-dashboard.th>
                </x-slot:head>
                @foreach ($listings as $listing)
                    <tr class="hover:bg-muted/50">
                        <x-dashboard.td class="font-medium min-w-0">
                            <div class="text-text-primary">{{ $listing->title }}</div>
                            <div class="mt-0.5 text-xs text-text-muted">
                                Updated {{ $listing->updated_at->format('M j, Y') }}
                            </div>
                        </x-dashboard.td>
                        <x-dashboard.td>₦{{ number_format($listing->price, 2) }}</x-dashboard.td>
                        <x-dashboard.td>
                            @php
                                $badgeStatus = match ($listing->status) {
                                    'published' => 'completed',
                                    'pending_review' => 'pending',
                                    'rejected' => 'rejected',
                                    'draft' => 'neutral',
                                    'archived' => 'neutral',
                                    'suspended' => 'danger',
                                    default => 'default',
                                };
                            @endphp
                            <x-dashboard.badge :status="$badgeStatus">{{ str_replace('_', ' ', $listing->status) }}</x-dashboard.badge>
                        </x-dashboard.td>
                        <x-dashboard.td>
                            @include('dashboard.user.listings._actions', ['listing' => $listing, 'mode' => 'menu'])
                        </x-dashboard.td>
                    </tr>
                @endforeach
            </x-dashboard.table>
        </div>
    @endif

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$listings" />
    </x-slot:pagination>
</x-layout.page>
@endsection
