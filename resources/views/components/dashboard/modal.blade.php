@props([
    'name' => 'dashboard-modal',
    'title' => null,
])

<x-ui.modal :name="$name" :title="$title" {{ $attributes }}>
    {{ $slot }}
    @isset($form)
        <x-slot:form>{{ $form }}</x-slot:form>
    @endisset
    @isset($footer)
        <x-slot:footer>{{ $footer }}</x-slot:footer>
    @endisset
</x-ui.modal>
