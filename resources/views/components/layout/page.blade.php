@props([
    'title',
    'subtitle' => null,
    'width' => 'full',
    'breadcrumb' => [],
])

{{--
  Authenticated dashboard page shell.
  All dashboard widths resolve to full-bleed of the main content area so
  list, detail, and create/edit pages share one consistent chrome.
  (Do not reintroduce narrow form/content caps on the page shell.)
--}}
@php
    $widths = [
        'content-sm' => 'max-w-content-full',
        'content-md' => 'max-w-content-full',
        'content' => 'max-w-content-full',
        'content-lg' => 'max-w-content-full',
        'full' => 'max-w-content-full',
        'form' => 'max-w-content-full',
        'auth' => 'max-w-auth',
        'marketing' => 'max-w-marketing',
    ];
    $max = $widths[$width] ?? $widths['full'];
@endphp

<div {{ $attributes->merge(['class' => $max . ' mx-auto w-full space-y-section']) }}>
    @if (count($breadcrumb))
        <x-ui.breadcrumb :items="$breadcrumb" />
    @endif

    <x-ui.page-header :title="$title" :subtitle="$subtitle" class="!mb-0">
        @isset($actions)
            <x-slot:actions>{{ $actions }}</x-slot:actions>
        @endisset
    </x-ui.page-header>

    <div class="space-y-section">
        {{ $slot }}
    </div>

    @isset($pagination)
        <div>{{ $pagination }}</div>
    @endisset
</div>
