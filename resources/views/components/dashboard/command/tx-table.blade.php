@props([
    'rows' => [],
    'viewAllUrl' => null,
    'title' => 'Recent Transactions',
])

<div {{ $attributes->class(['overflow-hidden rounded-2xl border border-border-default bg-elevated shadow-sm']) }}>
    <div class="flex items-center justify-between border-b border-border-subtle p-5">
        <h3 class="text-sm font-bold text-text-primary">{{ $title }}</h3>
        @if ($viewAllUrl)
            <a href="{{ $viewAllUrl }}" class="text-[11px] font-bold text-brand hover:underline">View All</a>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="border-b border-border-subtle bg-muted/30">
                <tr>
                    <th class="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-text-muted">Reference</th>
                    <th class="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-text-muted">Client / Actor</th>
                    <th class="px-6 py-3 text-right text-[10px] font-black uppercase tracking-widest text-text-muted">Amount (NGN)</th>
                    <th class="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-text-muted">Status</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @forelse ($rows as $i => $row)
                    <tr class="border-b border-border-subtle transition-colors hover:bg-muted/40 {{ $i % 2 === 1 ? 'bg-muted/20' : '' }}">
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs font-bold text-text-primary">#{{ $row['reference'] ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-[10px] font-black text-emerald-700">
                                    {{ $row['user_initials'] ?? '?' }}
                                </div>
                                <span class="font-bold text-text-secondary">{{ $row['user_name'] ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-text-primary">{{ number_format((float) ($row['amount'] ?? 0), 2) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-black uppercase text-emerald-600">{{ $row['status'] ?? '—' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-sm text-text-muted">No transactions yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
