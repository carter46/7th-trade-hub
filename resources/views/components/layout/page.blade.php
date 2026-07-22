@props([
    'title',
    'subtitle' => null,
    'width' => 'content',
    'breadcrumb' => [],
])

{{--
  Width conventions (authenticated dashboards):
  - full: data tables / dense indexes / admin queues
  - content: dashboards, multi-card detail, long multi-section forms
  - form: short create/edit forms
  - content-md: legacy; prefer form for short forms
--}}
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
