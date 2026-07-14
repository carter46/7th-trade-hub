@props(['count' => 4])

<div {{ $attributes->merge(['class' => 'grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4']) }}>
    @for ($i = 0; $i < $count; $i++)
        <div class="rounded-2xl border border-border-default bg-elevated/40 p-5 min-h-[120px] animate-pulse">
            <div class="h-3 w-24 rounded bg-muted"></div>
            <div class="mt-6 h-7 w-32 rounded bg-muted"></div>
        </div>
    @endfor
</div>
