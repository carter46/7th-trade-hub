@props([
    'striped' => false,
    'loading' => false,
    'empty' => false,
    'emptyTitle' => 'No records yet',
    'emptyDescription' => 'There’s nothing to show here right now.',
    'emptyIcon' => 'empty',
    'emptyAction' => null,
    'minHeight' => true,
])

{{-- Internals: single implementation lives in x-dashboard.table --}}
<x-dashboard.table
    :striped="$striped"
    :loading="$loading"
    :empty="$empty"
    :empty-title="$emptyTitle"
    :empty-description="$emptyDescription"
    :empty-icon="$emptyIcon"
    :empty-action="$emptyAction"
    :min-height="$minHeight"
    {{ $attributes }}
>
    @isset($toolbar)
        <x-slot:toolbar>{{ $toolbar }}</x-slot:toolbar>
    @endisset
    @isset($filters)
        <x-slot:filters>{{ $filters }}</x-slot:filters>
    @endisset
    @isset($bulk)
        <x-slot:bulk>{{ $bulk }}</x-slot:bulk>
    @endisset
    @isset($head)
        <x-slot:head>{{ $head }}</x-slot:head>
    @endisset
    @isset($footer)
        <x-slot:footer>{{ $footer }}</x-slot:footer>
    @endisset
    {{ $slot }}
</x-dashboard.table>
