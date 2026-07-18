@php
    $breadcrumbs = $breadcrumbs ?? [];
    $title = $title ?? '';
    $subtitle = $subtitle ?? null;
@endphp
<header class="mb-8 sm:mb-10">
    @if(count($breadcrumbs))
        <nav class="mb-3 text-sm text-slate-400" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-1.5">
                @foreach($breadcrumbs as $i => $crumb)
                    <li class="inline-flex items-center gap-1.5">
                        @if($i > 0)
                            <span class="text-slate-600" aria-hidden="true">/</span>
                        @endif
                        @if(!empty($crumb['href']) && ! $loop->last)
                            <a href="{{ $crumb['href'] }}" class="hover:text-accent transition-colors">{{ $crumb['label'] }}</a>
                        @else
                            <span class="{{ $loop->last ? 'text-slate-300' : '' }}">{{ $crumb['label'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif

    <h1 class="text-2xl sm:text-3xl font-bold font-display text-white">{{ $title }}</h1>
    @if($subtitle)
        <p class="mt-2 text-slate-400 max-w-2xl">{{ $subtitle }}</p>
    @endif
</header>
