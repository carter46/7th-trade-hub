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

{{-- Shared site modal: white panel, consistent actions. Open via $dispatch('open-modal', 'name') --}}
<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
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
            <div
                class="absolute inset-0 bg-slate-950/50 backdrop-blur-[2px]"
                @click="open = false"
                x-transition.opacity
            ></div>
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl"
                @click.stop
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 id="modal-title-{{ $name }}" class="text-lg font-semibold text-slate-900">{{ $title }}</h2>
                        @if ($description)
                            <p class="mt-1.5 text-sm text-slate-600 leading-relaxed">{{ $description }}</p>
                        @endif
                    </div>
                    <button
                        type="button"
                        class="shrink-0 rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors"
                        @click="open = false"
                        aria-label="Close"
                    >
                        <x-ui.icon name="x" class="w-4 h-4" />
                    </button>
                </div>

                @if ($slot->isNotEmpty())
                    <div class="mt-4 text-sm text-slate-600">{{ $slot }}</div>
                @endif

                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                    <x-ui.button type="button" variant="secondary" @click="open = false">{{ $cancelLabel }}</x-ui.button>
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
