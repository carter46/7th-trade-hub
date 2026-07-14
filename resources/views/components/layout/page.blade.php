@props([
    'title',
    'subtitle' => null,
    'width' => 'content',
    'breadcrumb' => [],
])

@php
    $widths = [
        'content-sm' => 'max-w-content-sm',
        'content-md' => 'max-w-content-md',
        'content' => 'max-w-content',
        'content-lg' => 'max-w-content-lg',
        'full' => 'max-w-content-full',
        'form' => 'max-w-form',
        'auth' => 'max-w-auth',
        'marketing' => 'max-w-marketing',
    ];
    $max = $widths[$width] ?? $widths['content'];
@endphp

<div {{ $attributes->merge(['class' => $max . ' mx-auto w-full space-y-section']) }}>
    <x-ui.page-header :title="$title" :subtitle="$subtitle">
        @isset($actions)
            <x-slot:actions>{{ $actions }}</x-slot:actions>
        @endisset
    </x-ui.page-header>

    @if (count($breadcrumb))
        <x-ui.breadcrumb :items="$breadcrumb" />
    @endif

    <div class="space-y-section">
        {{ $slot }}
    </div>

    @isset($pagination)
        <div>{{ $pagination }}</div>
    @endisset
</div>
