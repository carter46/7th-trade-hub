@extends('layouts.dashboard-admin')

@section('title', 'Add Demo Integration')

@section('content')
<x-layout.page
    title="Add Demo Integration"
    subtitle="Select an existing Website Package product, then enter the independent demo site details."
    width="narrow"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Demo Site Integrate', route('admin.site-integrations')],
        ['Create', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.site-integrations.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium text-text-primary">Select product</label>
                <x-dashboard.select name="platform_product_id" required>
                    <option value="">Choose a website package…</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected(old('platform_product_id') == $product->id)>{{ $product->title }}</option>
                    @endforeach
                </x-dashboard.select>
                @if ($products->isEmpty())
                    <p class="mt-2 text-sm text-amber-600">All website package products already have an integration, or none exist yet.</p>
                @endif
            </div>
            <x-dashboard.input name="name" label="Display name (optional)" :value="old('name')" />
            <x-dashboard.input name="base_url" label="Demo site base URL" type="url" :value="old('base_url')" required placeholder="https://demo.example.com" />
            <x-dashboard.input name="demo_user_email" label="Demo user email" type="email" :value="old('demo_user_email')" />
            <x-dashboard.input name="demo_admin_email" label="Demo admin email" type="email" :value="old('demo_admin_email')" />
            <fieldset class="space-y-2">
                <legend class="text-sm font-medium text-text-primary">Capabilities</legend>
                @foreach ($defaultCapabilities as $cap)
                    <label class="flex items-center gap-2 text-sm text-text-secondary">
                        <input type="checkbox" name="capabilities[]" value="{{ $cap }}" checked class="rounded border-border-default">
                        {{ $cap }}
                    </label>
                @endforeach
            </fieldset>
            <div class="flex justify-end gap-2">
                <x-dashboard.button :href="route('admin.site-integrations')" variant="secondary">Cancel</x-dashboard.button>
                <x-dashboard.button type="submit" :disabled="$products->isEmpty()">Generate API keys</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
