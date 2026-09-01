@extends('layouts.dashboard-user')

@section('title', 'Services')

@section('content')
<x-layout.page
    title="Services"
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Services', null],
    ]"
>
    <div class="space-y-8">
        <x-dashboard.card>
            <form method="GET" class="flex flex-wrap gap-3">
                <div class="min-w-[16rem] flex-1">
                    <x-dashboard.input name="q" :value="$q" placeholder="Search services..." />
                </div>
                <x-dashboard.button type="submit" size="sm" icon="search">Search</x-dashboard.button>
            </form>
        </x-dashboard.card>

        @if($searchResults)
            <section>
                <h2 class="text-sm font-semibold text-text-secondary uppercase tracking-wide mb-3">Search results</h2>
                <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                    @forelse($searchResults as $product)
                        @include('dashboard.user.partials.service-product-card', ['product' => $product])
                    @empty
                        <p class="col-span-full text-text-muted">No services matched.</p>
                    @endforelse
                </div>
                <div class="mt-4">
                    <x-dashboard.pagination :paginator="$searchResults" />
                </div>
            </section>
        @else
            <section>
                <h2 class="text-sm font-semibold text-text-secondary uppercase tracking-wide mb-3">Categories</h2>
                <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                    @foreach($groups as $group)
                        @php
                            $image = $group['card_image'] ?? null;
                            $imageSrc = null;
                            if (is_string($image) && $image !== '') {
                                $trimmed = trim($image);
                                if (preg_match('#^(https?:)?//#i', $trimmed) || str_starts_with($trimmed, '/')) {
                                    $imageSrc = $trimmed;
                                } else {
                                    $imageSrc = asset(ltrim(str_replace('\\', '/', $trimmed), '/'));
                                }
                            }
                            $label = $group['label'] ?? $group['name'] ?? 'Category';
                            $initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $label) ?: 'S', 0, 2));
                        @endphp
                        <x-dashboard.card :padding="false" class="flex h-full flex-col overflow-hidden">
                            <div class="p-3">
                                <div class="relative aspect-[4/3] overflow-hidden rounded-lg bg-muted">
                                    @if($imageSrc)
                                        <img src="{{ $imageSrc }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary/40 via-muted to-elevated">
                                            <span class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-white/15 text-xs font-bold text-white" aria-hidden="true">
                                                {{ $initials }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-1 flex-col space-y-2 px-4 pb-4">
                                <div class="font-semibold text-text-primary">{{ $label }}</div>
                                @if(!empty($group['short_description']))
                                    <p class="text-sm text-text-secondary line-clamp-2">{{ $group['short_description'] }}</p>
                                @endif
                                @if(!empty($group['href']))
                                    <div class="mt-auto pt-2">
                                        <x-dashboard.button :href="$group['href']" size="xs" class="w-full sm:w-auto">
                                            {{ $group['cta'] ?? 'Browse' }}
                                        </x-dashboard.button>
                                    </div>
                                @endif
                            </div>
                        </x-dashboard.card>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layout.page>
@endsection
