@props([
    'label' => 'Sidebar navigation',
])

<nav role="navigation" aria-label="{{ $label }}" {{ $attributes }}>
    {{ $slot }}
</nav>
