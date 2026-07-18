@php
    $breadcrumbs = $breadcrumbs ?? [];
    $title = $title ?? '';
    $subtitle = $subtitle ?? null;
    $image = $image ?? null;
    $cta = $cta ?? null; // optional ['label' => '', 'href' => '']
@endphp
<header class="relative overflow-hidden rounded-2xl border border-white/10 mb-8 sm:mb-10">
    <div class="absolute inset-0">
        @if($image)
            <img src="{{ asset($image) }}" alt="" class="h-full w-full object-cover">
        @else
            <div class="h-full w-full marketing-page-hero-bg" aria-hidden="true"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/95 via-slate-950/80 to-slate-950/55"></div>
        <div class="absolute inset-0 bg-primary/20"></div>
    </div>

    <div class="relative px-5 sm:px-8 py-8 sm:py-10">
        @if(count($breadcrumbs))
            <nav class="mb-3 text-sm text-slate-300" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-1.5">
                    @foreach($breadcrumbs as $i => $crumb)
                        <li class="inline-flex items-center gap-1.5">
                            @if($i > 0)
                                <span class="text-slate-500" aria-hidden="true">/</span>
                            @endif
                            @if(!empty($crumb['href']) && ! $loop->last)
                                <a href="{{ $crumb['href'] }}" class="hover:text-white transition-colors">{{ $crumb['label'] }}</a>
                            @else
                                <span class="{{ $loop->last ? 'text-white/90' : '' }}">{{ $crumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <h1 class="text-2xl sm:text-3xl font-bold font-display text-white tracking-tight">{{ $title }}</h1>
        @if($subtitle)
            <p class="mt-2 text-slate-200/90 max-w-2xl text-sm sm:text-base leading-relaxed">{{ $subtitle }}</p>
        @endif

        @if(!empty($cta['href']) && !empty($cta['label']))
            <div class="mt-5">
                <a href="{{ $cta['href'] }}"
                   class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-slate-900 hover:bg-accent hover:text-white transition-colors">
                    {{ $cta['label'] }}
                </a>
            </div>
        @endif
    </div>
</header>
