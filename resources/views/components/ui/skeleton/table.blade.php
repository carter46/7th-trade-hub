@props(['rows' => 5, 'cols' => 5])

<div {{ $attributes->merge(['class' => 'p-4 space-y-3 animate-pulse min-h-[400px]']) }}>
    <div class="flex gap-4 pb-3 border-b border-border-default">
        @for ($c = 0; $c < $cols; $c++)
            <div class="h-3 flex-1 rounded bg-muted"></div>
        @endfor
    </div>
    @for ($r = 0; $r < $rows; $r++)
        <div class="flex gap-4 py-2">
            @for ($c = 0; $c < $cols; $c++)
                <div class="h-4 flex-1 rounded bg-muted/70"></div>
            @endfor
        </div>
    @endfor
</div>
