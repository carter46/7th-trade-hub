@extends('layouts.dashboard-admin')

@section('title', 'Edit Category')

@section('content')
<x-layout.page
    title="Edit Category"
    subtitle="Update category details and hierarchy."
    width="form"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Categories', route('admin.marketplace-categories')],
        ['Edit', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.marketplace-categories.update', $category) }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            <x-dashboard.input label="Name" name="name" :value="old('name', $category->name)" required />
            <x-dashboard.select label="Parent (optional)" name="parent_id">
                <option value="">Top level</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
                @endforeach
            </x-dashboard.select>
            <x-dashboard.input label="Sort order" name="sort_order" type="number" min="0" :value="old('sort_order', $category->sort_order)" />

            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save changes</x-dashboard.button>
                <x-dashboard.button :href="route('admin.marketplace-categories')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
