@extends('layouts.dashboard-user')

@section('title', 'Create Listing')

@section('content')
<x-layout.page
    title="Create Listing"
    subtitle="Save a draft, then submit it for review from My Listings."
    width="form"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['My Listings', route('dashboard.listings')],
        ['Create', null],
    ]"
>
    <x-ui.card>
        <form method="POST" action="{{ route('dashboard.listings.store') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-ui.input label="Title" name="title" :value="old('title')" required />
            <x-ui.textarea label="Description" name="description">{{ old('description') }}</x-ui.textarea>
            <x-ui.select label="Category" name="category_id">
                <option value="">— Select —</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input label="Price (NGN)" type="number" name="price" min="1" :value="old('price')" required />
            <x-ui.button type="submit" icon="check" x-bind:disabled="submitting">Save Draft</x-ui.button>
        </form>
    </x-ui.card>
</x-layout.page>
@endsection
