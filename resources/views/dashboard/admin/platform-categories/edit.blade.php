@extends('layouts.dashboard-admin')

@section('title', 'Edit Platform Category')

@section('content')
<x-layout.page
    title="Edit Platform Category"
    subtitle="Update category details and marketing content."
    width="form"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Platform Categories', route('admin.platform-categories')],
        ['Edit', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.platform-categories.update', $category) }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')
            <x-dashboard.input label="Name" name="name" :value="old('name', $category->name)" required />
            <x-dashboard.select label="Product type" name="product_type" required>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(old('product_type', $category->product_type->value) === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </x-dashboard.select>
            <x-dashboard.input label="Sort order" name="sort_order" type="number" min="0" :value="old('sort_order', $category->sort_order)" />
            <x-dashboard.input label="Short description" name="short_description" :value="old('short_description', $category->short_description)" />
            <x-dashboard.input label="Hero title" name="hero_title" :value="old('hero_title', $category->hero_title)" />
            <x-dashboard.input label="Hero subtitle" name="hero_subtitle" :value="old('hero_subtitle', $category->hero_subtitle)" />
            <x-dashboard.input label="Banner image path" name="banner_image" :value="old('banner_image', $category->banner_image)" placeholder="/images/..." />
            <x-dashboard.input label="Card image path" name="card_image" :value="old('card_image', $category->card_image)" placeholder="/images/..." />
            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save changes</x-dashboard.button>
                <x-dashboard.button :href="route('admin.platform-categories')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
