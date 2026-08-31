@php
    $activeTab = $activeTab ?? 'websites';
@endphp

<nav class="flex flex-wrap gap-1 border-b border-border-default" aria-label="My Tools sections">
    <a
        href="{{ route('dashboard.my-tools') }}"
        @class([
            'inline-flex min-h-11 items-center border-b-2 px-4 py-2 text-sm font-medium transition-colors focus-ring',
            $activeTab === 'websites'
                ? 'border-primary text-primary'
                : 'border-transparent text-text-secondary hover:text-text-primary',
        ])
        @if ($activeTab === 'websites') aria-current="page" @endif
    >
        My Websites
    </a>
    <a
        href="{{ route('dashboard.my-tools.domains') }}"
        @class([
            'inline-flex min-h-11 items-center border-b-2 px-4 py-2 text-sm font-medium transition-colors focus-ring',
            $activeTab === 'domains'
                ? 'border-primary text-primary'
                : 'border-transparent text-text-secondary hover:text-text-primary',
        ])
        @if ($activeTab === 'domains') aria-current="page" @endif
    >
        My Domains
    </a>
</nav>
