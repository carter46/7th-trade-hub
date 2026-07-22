@extends('layouts.dashboard-admin')

@section('title', 'Add Platform Category')

@section('content')
<x-layout.page
    title="Add Platform Category"
    subtitle="Create a category for platform products."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Platform Categories', route('admin.platform-categories')],
        ['Create', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.platform-categories.store') }}" class="max-w-form space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-dashboard.input label="Name" name="name" :value="old('name')" required />
            <x-dashboard.select label="Product type" name="product_type" required>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(old('product_type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </x-dashboard.select>
            <x-dashboard.input label="Sort order" name="sort_order" type="number" min="0" :value="old('sort_order', 0)" />
            <x-dashboard.input label="Short description" name="short_description" :value="old('short_description')" />
            <x-dashboard.input label="Hero title" name="hero_title" :value="old('hero_title')" />
            <x-dashboard.input label="Hero subtitle" name="hero_subtitle" :value="old('hero_subtitle')" />
            <x-dashboard.input label="Banner image path" name="banner_image" :value="old('banner_image')" placeholder="/images/..." />
            <x-dashboard.input label="Card image path" name="card_image" :value="old('card_image')" placeholder="/images/..." />
            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Create category</x-dashboard.button>
                <x-dashboard.button :href="route('admin.platform-categories')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
