@extends('layouts.dashboard-admin')

@section('title', 'Add Category')

@section('content')
<x-layout.page
    title="Add Category"
    subtitle="Create a marketplace category for listings."
    width="form"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Categories', route('admin.marketplace-categories')],
        ['Create', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.marketplace-categories.store') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <x-dashboard.input label="Name" name="name" :value="old('name')" required />
            <x-dashboard.select label="Parent (optional)" name="parent_id">
                <option value="">Top level</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" @selected(old('parent_id') == $parent->id)>{{ $parent->name }}</option>
                @endforeach
            </x-dashboard.select>
            <x-dashboard.input label="Sort order" name="sort_order" type="number" min="0" :value="old('sort_order', 0)" />

            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Create category</x-dashboard.button>
                <x-dashboard.button :href="route('admin.marketplace-categories')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
