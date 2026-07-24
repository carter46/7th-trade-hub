@extends('layouts.dashboard-admin')

@section('title', 'Analytics')

@section('content')
@php
    $section = $section ?? 'revenue';
    $rangeKey = $range['range'] ?? ($filters['range'] ?? '30d');
    $sectionLabels = [
        'traffic' => 'Traffic',
        'revenue' => 'Revenue',
        'marketplace' => 'Marketplace',
        'services' => 'Services',
        'escrows' => 'Escrows',
        'users' => 'Users',
        'support' => 'Support',
        'kyc' => 'KYC',
    ];
@endphp

<div
    class="space-y-6"
    data-command-analytics
    data-analytics-endpoint="{{ route('admin.analytics') }}"
    data-section="{{ $section }}"
>
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-text-muted">
                <span>Admin</span><span aria-hidden="true">/</span><span class="text-brand">Analytics</span>
            </nav>
            <h1 class="text-3xl font-bold tracking-tight text-text-primary">Analytics</h1>
            <p class="mt-1 text-sm text-text-secondary">Drill-down reports · {{ $sectionLabels[$section] ?? ucfirst($section) }}</p>
        </div>
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label for="analytics-section" class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-muted">Analytics type</label>
                <select
                    id="analytics-section"
                    data-analytics-section
                    class="min-w-[12rem] rounded-lg border border-border-default bg-elevated px-3 py-2 text-sm font-bold text-text-primary"
                >
                    @foreach ($sections ?? [] as $s)
                        <option value="{{ $s }}" @selected($s === $section)>{{ $sectionLabels[$s] ?? ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <x-dashboard.command.range-pills :value="$rangeKey" />
        </div>
    </header>

    <div id="analytics-report" class="relative min-h-[12rem]">
        @include('dashboard.admin.partials.analytics-report')
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-command-analytics]');
    if (!root) return;

    const endpoint = root.dataset.analyticsEndpoint;
    const panel = document.getElementById('analytics-report');
    const sectionSelect = root.querySelector('[data-analytics-section]');
    const wrap = root.querySelector('[data-command-range]');

    const currentRange = () => wrap?.querySelector('[data-range-select]')?.value || '30d';
    const setPillActive = (range) => {
        wrap?.querySelector('[data-custom-range]')?.classList.toggle('hidden', range !== 'custom');
        wrap?.querySelectorAll('[data-range-value]').forEach((btn) => {
            const active = btn.dataset.rangeValue === range;
            btn.classList.toggle('bg-elevated', active);
            btn.classList.toggle('shadow-sm', active);
            btn.classList.toggle('text-text-primary', active);
            btn.classList.toggle('text-text-muted', !active);
        });
        const select = wrap?.querySelector('[data-range-select]');
        if (select) select.value = range;
    };

    const load = async ({ section, range, from, to, push = true } = {}) => {
        const nextSection = section || sectionSelect?.value || root.dataset.section;
        const nextRange = range || currentRange();
        const params = new URLSearchParams({
            section: nextSection,
            range: nextRange,
            partial: '1',
            persist_range: '1',
        });
        if (nextRange === 'custom') {
            params.set('from', from || wrap?.querySelector('[data-range-from]')?.value || '');
            params.set('to', to || wrap?.querySelector('[data-range-to]')?.value || '');
            if (!params.get('from') || !params.get('to')) {
                setPillActive(nextRange);
                return;
            }
        }
        setPillActive(nextRange);
        panel?.setAttribute('aria-busy', 'true');
        panel?.classList.add('command-skeleton');
        try {
            const res = await fetch(`${endpoint}?${params}`, {
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Dashboard-Tab': '1',
                },
                credentials: 'same-origin',
            });
            if (!res.ok) {
                window.location.href = `${endpoint}?${params}`;
                return;
            }
            const html = await res.text();
            if (panel) {
                panel.innerHTML = html;
                window.mountCommandCharts?.(panel);
            }
            root.dataset.section = nextSection;
            const url = new URL(window.location.href);
            url.searchParams.set('section', nextSection);
            url.searchParams.set('range', nextRange);
            if (nextRange === 'custom') {
                url.searchParams.set('from', params.get('from'));
                url.searchParams.set('to', params.get('to'));
            } else {
                url.searchParams.delete('from');
                url.searchParams.delete('to');
            }
            if (push) history.pushState({ analytics: true }, '', url);
            else history.replaceState({ analytics: true }, '', url);
        } catch (_) {
            // keep current panel
        } finally {
            panel?.removeAttribute('aria-busy');
            panel?.classList.remove('command-skeleton');
        }
    };

    sectionSelect?.addEventListener('change', () => load({ section: sectionSelect.value, push: true }));
    wrap?.querySelectorAll('[data-range-value]').forEach((btn) => {
        btn.addEventListener('click', () => load({ range: btn.dataset.rangeValue, push: true }));
    });
    wrap?.querySelector('[data-range-select]')?.addEventListener('change', (e) => {
        load({ range: e.target.value, push: true });
    });
    wrap?.querySelector('[data-range-from]')?.addEventListener('change', () => {
        if (currentRange() === 'custom') load({ range: 'custom', push: true });
    });
    wrap?.querySelector('[data-range-to]')?.addEventListener('change', () => {
        if (currentRange() === 'custom') load({ range: 'custom', push: true });
    });

    window.addEventListener('popstate', () => window.location.reload());
    window.mountCommandCharts?.(panel);
});
</script>
@endpush
@endsection
