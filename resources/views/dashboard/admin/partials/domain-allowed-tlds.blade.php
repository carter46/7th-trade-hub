@php
    use App\Support\Domains\DomainProductTldPolicy;

    $presetTlds = DomainProductTldPolicy::defaultFeaturedTlds();
    $allowedTlds = DomainProductTldPolicy::allowedTlds($product);
    $allowedSet = array_fill_keys($allowedTlds, true);
    $registryTlds = collect($registryTlds ?? [])
        ->pluck('tld')
        ->map(fn ($tld) => ltrim(strtolower((string) $tld), '.'))
        ->unique()
        ->values()
        ->all();
    $advancedPool = array_values(array_diff($registryTlds, $presetTlds));
@endphp

<div
    class="space-y-4 rounded-xl border border-border-subtle px-4 py-4"
    x-data="{
        advancedOpen: false,
        advancedQuery: '',
        matches(tld) {
            const q = (this.advancedQuery || '').trim().toLowerCase();
            if (!q) return true;
            return tld.includes(q) || ('.' + tld).includes(q);
        },
    }"
>
    <div>
        <p class="text-sm font-medium text-text-primary">Allowed extensions</p>
        <p class="mt-1 text-xs text-text-muted">
            Checked extensions appear in the customer search dropdown. Additional selections are available under advanced search only.
        </p>
    </div>

    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
        @foreach ($presetTlds as $tld)
            <label class="flex items-center gap-2 rounded-lg border border-border-default px-2.5 py-2 text-sm">
                <input
                    type="checkbox"
                    name="allowed_tlds[]"
                    value="{{ $tld }}"
                    @checked(old('allowed_tlds') ? in_array($tld, old('allowed_tlds', []), true) : isset($allowedSet[$tld]))
                    class="accent-primary"
                >
                <span>.{{ $tld }}</span>
            </label>
        @endforeach
    </div>

    @error('allowed_tlds')
        <p class="text-xs text-danger">{{ $message }}</p>
    @enderror

    <div class="border-t border-border-default pt-3">
        <button
            type="button"
            class="text-sm font-medium text-primary hover:underline focus-ring"
            @click="advancedOpen = !advancedOpen"
        >
            <span x-text="advancedOpen ? 'Hide advanced extensions' : 'Advanced extension search'"></span>
        </button>

        <div x-show="advancedOpen" x-cloak class="mt-3 space-y-3">
            <input
                type="search"
                x-model="advancedQuery"
                placeholder="Search extensions (e.g. io, dev)..."
                class="w-full rounded-lg border-border-default bg-elevated text-sm"
            >
            <div class="max-h-64 overflow-y-auto rounded-xl border border-border-default p-3">
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
                    @foreach ($advancedPool as $tld)
                        <label
                            class="flex items-center gap-2 rounded-lg border border-border-default px-2.5 py-2 text-sm"
                            x-show="matches('{{ $tld }}')"
                        >
                            <input
                                type="checkbox"
                                name="allowed_tlds[]"
                                value="{{ $tld }}"
                                @checked(old('allowed_tlds') ? in_array($tld, old('allowed_tlds', []), true) : isset($allowedSet[$tld]))
                                class="accent-primary"
                            >
                            <span>.{{ $tld }}</span>
                        </label>
                    @endforeach
                </div>
                @if ($advancedPool === [])
                    <p class="text-sm text-text-muted">No additional registry extensions loaded. Check domain provider connection.</p>
                @endif
            </div>
        </div>
    </div>
</div>
