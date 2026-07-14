@extends('layouts.dashboard-user')

@section('title', 'Edit Listing')

@section('content')
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
        <form method="POST" action="{{ route('dashboard.listings.update', $listing) }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')
            <x-ui.input label="Title" name="title" :value="old('title', $version->title)" required />
            <x-ui.textarea label="Description" name="description">{{ old('description', $version->description) }}</x-ui.textarea>
            <x-ui.select label="Category" name="category_id">
                <option value="">— Select —</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $listing->category_id) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input label="Price (NGN)" type="number" name="price" min="1" :value="old('price', $version->price)" required />
            <x-ui.button type="submit" icon="check" x-bind:disabled="submitting">Save Draft</x-ui.button>
        </form>
    </x-ui.card>
</x-layout.page>
@endsection
