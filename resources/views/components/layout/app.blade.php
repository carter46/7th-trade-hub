{{-- Optional thin shell wrapper for shared dashboard content padding --}}
<div {{ $attributes->merge(['class' => 'w-full']) }}>
    {{ $slot }}
</div>
