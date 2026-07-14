@props([
    'name',
    'title' => 'Confirm',
    'description' => null,
    'confirmLabel' => 'Confirm',
    'cancelLabel' => 'Cancel',
    'variant' => 'default',
    'formAction' => null,
    'method' => 'POST',
])

@php
    $confirmVariant = match ($variant) {
        'danger' => 'danger',
        'warning' => 'warning',
        default => 'primary',
    };
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:keydown.escape.window="open = false"
    {{ $attributes }}
>
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-[90] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            :aria-labelledby="'modal-title-{{ $name }}'"
        >
            <div class="absolute inset-0 bg-black/60" @click="open = false"></div>
            <div
                x-show="open"
                x-transition
                class="relative w-full max-w-md rounded-2xl border border-border-default bg-elevated p-6 shadow-xl"
                @click.stop
            >
                <h2 id="modal-title-{{ $name }}" class="text-lg font-semibold text-text-primary">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-2 text-sm text-text-secondary">{{ $description }}</p>
                @endif
                <div class="mt-3 text-sm text-text-secondary">{{ $slot }}</div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <x-ui.button type="button" variant="ghost" @click="open = false">{{ $cancelLabel }}</x-ui.button>
                    @if ($formAction)
                        <form method="POST" action="{{ $formAction }}" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            @if (strtoupper($method) !== 'POST')
                                @method($method)
                            @endif
                            @isset($form)
                                {{ $form }}
                            @endisset
                            <x-ui.button type="submit" :variant="$confirmVariant" x-bind:disabled="submitting">
                                <span class="inline-flex items-center gap-2">
                                    <span x-show="submitting" x-cloak><x-ui.icon name="spinner" class="w-4 h-4 animate-spin" /></span>
                                    {{ $confirmLabel }}
                                </span>
                            </x-ui.button>
                        </form>
                    @else
                        <x-ui.button type="button" :variant="$confirmVariant" @click="open = false; $dispatch('modal-confirmed', '{{ $name }}')">
                            {{ $confirmLabel }}
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>
