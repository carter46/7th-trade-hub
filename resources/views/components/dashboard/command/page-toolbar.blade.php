@props([
    'greeting' => 'Overview',
    'subtitle' => null,
    'breadcrumb' => null,
    'range' => '7d',
    'showExport' => false,
    'exportUrl' => null,
])

<header {{ $attributes->class(['flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between']) }}>
    <div>
        @if ($breadcrumb)
            <nav class="mb-1 flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-text-muted">
                @foreach ($breadcrumb as $i => $crumb)
                    @if ($i > 0)
                        <span aria-hidden="true">/</span>
                    @endif
                    <span class="{{ $loop->last ? 'text-brand' : '' }}">{{ is_array($crumb) ? ($crumb[0] ?? '') : $crumb }}</span>
                @endforeach
            </nav>
        @endif
        <h1 class="text-3xl font-bold tracking-tight text-text-primary">{{ $greeting }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-text-secondary">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <x-dashboard.command.range-pills :value="$range" />
        @if ($showExport && $exportUrl)
            <a href="{{ $exportUrl }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white hover:bg-slate-800 dark:bg-elevated dark:text-text-primary dark:border dark:border-border-default">
                Export
            </a>
        @endif
    </div>
</header>
