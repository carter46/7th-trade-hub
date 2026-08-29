@extends('layouts.dashboard-admin')

@section('title', 'Edit Service Category')

@section('content')
<x-layout.page
    title="Edit Service Category"
    subtitle="Fixed platform category — public name, position, and visibility."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Service Categories', route('admin.service-categories')],
        ['Edit', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.service-categories.update', $category) }}" class="w-full space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')
            <x-dashboard.input label="Public name" name="name" :value="old('name', $category->name)" required />
            <p class="text-xs text-text-muted">URL slug is frozen: <span class="font-mono">{{ $category->slug }}</span></p>
            <x-dashboard.input
                label="Sort position"
                name="sort_order"
                type="number"
                min="1"
                :max="$siblingMax"
                :value="old('sort_order', $category->sort_order)"
                required
            />
            <p class="text-xs text-text-muted">Position among all platform categories (1–{{ $siblingMax }}). Neighbors shift automatically.</p>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))> Active</label>
            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save</x-dashboard.button>
                <x-dashboard.button :href="route('admin.service-categories')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
