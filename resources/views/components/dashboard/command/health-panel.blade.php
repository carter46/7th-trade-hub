@props([
    'metrics' => [],
    'uptime' => null,
    'title' => 'System Health',
])

<div {{ $attributes->class(['rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-xl']) }}>
    <div class="mb-6 flex items-center justify-between">
        <h3 class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-white/40">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> {{ $title }}
        </h3>
        @if ($uptime)
            <span class="text-[10px] font-bold text-emerald-400">{{ $uptime }}</span>
        @else
            <span class="text-[10px] font-bold text-white/40">N/A</span>
        @endif
    </div>
    @if (count($metrics) === 0)
        <p class="text-xs text-white/50">Monitoring not configured for this environment.</p>
    @else
        <div class="space-y-4">
            @foreach ($metrics as $metric)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-1.5 w-1.5 rounded-full {{ ($metric['ok'] ?? true) ? 'bg-emerald-400' : 'bg-red-400' }}"></div>
                        <span class="text-[10px] font-bold uppercase text-white/70">{{ $metric['label'] ?? '' }}</span>
                    </div>
                    <span class="font-mono text-[10px] {{ ($metric['ok'] ?? true) ? 'text-white/40' : 'text-red-400 font-black' }}">{{ $metric['value'] ?? '—' }}</span>
                </div>
            @endforeach
        </div>
    @endif
    <a href="{{ route('admin.monitoring') }}" class="mt-6 block w-full rounded-lg border border-white/10 py-2 text-center text-[10px] font-black uppercase tracking-widest text-white/50 hover:text-emerald-400">System console</a>
</div>
