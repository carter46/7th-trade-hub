@extends('layouts.dashboard-admin')

@section('title', 'Edit Service')

@section('content')
<x-layout.page
    title="Edit Service"
    subtitle="Fixed platform service — public name, position within category, and visibility."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Services', route('admin.services')],
        ['Edit', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.services.update', $service) }}" class="w-full space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')
            <x-dashboard.input label="Public name" name="name" :value="old('name', $service->name)" required />
            <p class="text-xs text-text-muted">
                Category: {{ $service->serviceCategory?->name ?? '—' }}
                · Slug frozen: <span class="font-mono">{{ $service->slug }}</span>
            </p>
            <x-dashboard.input
                label="Sort position"
                name="sort_order"
                type="number"
                min="1"
                :max="$siblingMax"
                :value="old('sort_order', $service->sort_order)"
                required
            />
            <p class="text-xs text-text-muted">Position within this category (1–{{ $siblingMax }}). Neighbors in the same category shift automatically.</p>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active))> Active</label>
            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save</x-dashboard.button>
                <x-dashboard.button :href="route('admin.services')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
