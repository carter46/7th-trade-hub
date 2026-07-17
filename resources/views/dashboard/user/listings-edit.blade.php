@extends('layouts.dashboard-user')

@section('title', 'Edit Listing')

@section('content')
@php
    $tree = $parents->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'children' => $p->children->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values(),
    ])->values();
@endphp
<x-layout.page
    title="Edit Listing"
    subtitle="Version {{ $version->version_number }} — {{ $version->status }}"
    width="form"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['My Listings', route('dashboard.listings')],
        ['Edit', null],
    ]"
>
    <x-ui.card>
        <form
            method="POST"
            action="{{ route('dashboard.listings.update', $listing) }}"
            class="space-y-4"
            x-data="listingCategoryForm(@js($tree), {{ (int) old('parent_id', $selectedParentId) }}, {{ (int) old('category_id', $selectedCategoryId) }})"
            @submit="submitting = true"
        >
            @csrf
            @method('PUT')
            <x-ui.input label="Title" name="title" :value="old('title', $version->title)" required />
            <x-ui.textarea label="Description" name="description">{{ old('description', $version->description) }}</x-ui.textarea>

            <div>
                <label class="block text-sm font-medium text-text-secondary mb-1">Parent category</label>
                <select x-model.number="parentId" name="parent_id" class="w-full rounded-xl border-border-default bg-elevated" required>
                    <option value="0">— Select group —</option>
                    <template x-for="parent in parents" :key="parent.id">
                        <option :value="parent.id" x-text="parent.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-secondary mb-1">Subcategory</label>
                <select x-model.number="categoryId" name="category_id" class="w-full rounded-xl border-border-default bg-elevated" required>
                    <option value="0">— Select subcategory —</option>
                    <template x-for="child in children" :key="child.id">
                        <option :value="child.id" x-text="child.name"></option>
                    </template>
                </select>
                @error('category_id')<p class="text-danger text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <x-ui.input label="Price (NGN)" type="number" name="price" min="1" :value="old('price', $version->price)" required />
            <x-ui.button type="submit" icon="check" x-bind:disabled="submitting">Save Draft</x-ui.button>
        </form>
    </x-ui.card>
</x-layout.page>
@endsection
