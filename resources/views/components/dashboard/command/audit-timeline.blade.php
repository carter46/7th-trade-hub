@props([
    'entries' => [],
    'consoleUrl' => null,
    'title' => 'Security Audit',
])

<div {{ $attributes->class(['rounded-2xl border border-border-default bg-elevated p-5 shadow-sm']) }}>
    <h3 class="mb-6 text-xs font-black uppercase tracking-widest text-text-muted">{{ $title }}</h3>
    <div class="relative space-y-6 before:absolute before:inset-y-0 before:left-[11px] before:w-px before:bg-border-subtle">
        @forelse ($entries as $entry)
            <div class="relative flex gap-4">
                <div class="relative z-10 flex h-6 w-6 items-center justify-center rounded-full border-2 border-border-default bg-elevated {{ !empty($entry['severity']) ? 'border-red-500' : 'border-brand' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ !empty($entry['severity']) ? 'bg-red-500' : 'bg-brand' }}"></span>
                </div>
                <div class="flex-1">
                    <p class="text-[11px] font-bold leading-none text-text-primary">{{ $entry['action'] ?? '' }}</p>
                    <p class="mt-1 text-[10px] text-text-muted">{{ $entry['actor'] ?? '' }}</p>
                    <span class="mt-2 block text-[9px] text-text-muted">{{ $entry['when'] ?? '' }}</span>
                </div>
            </div>
        @empty
            <p class="pl-8 text-xs text-text-muted">No recent audit activity</p>
        @endforelse
    </div>
    @if ($consoleUrl)
        <a href="{{ $consoleUrl }}" class="mt-6 block w-full rounded-lg border border-border-subtle py-2 text-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-brand">Full Audit Console</a>
    @endif
</div>
