@props([
    'name' => 'dashboard-modal',
    'title' => null,
    'maxWidth' => 'md',
    'showActions' => true,
])

<x-ui.modal
    :name="$name"
    :title="$title"
    :max-width="$maxWidth"
    :show-actions="$showActions"
    {{ $attributes }}
>
    {{ $slot }}
    @isset($form)
        <x-slot:form>{{ $form }}</x-slot:form>
    @endisset
    @isset($footer)
        <x-slot:footer>{{ $footer }}</x-slot:footer>
    @endisset
</x-ui.modal>
