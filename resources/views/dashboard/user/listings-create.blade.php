@extends('layouts.dashboard-user')

@section('title', 'Create Listing')

@section('content')
@php
    $tree = $parents->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'products' => $p->products->map(fn ($prod) => ['id' => $prod->id, 'name' => $prod->name])->values(),
    ])->values();
@endphp
<x-layout.page
    title="Create Listing"
    subtitle="Pick a product category, then save a draft for review."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['My Listings', route('dashboard.listings')],
        ['Create', null],
    ]"
>
    <x-dashboard.card>
        <form
            method="POST"
            action="{{ route('dashboard.listings.store') }}"
            class="w-full space-y-4"
            x-data="listingProductForm(@js($tree), @js(old('category_id', '')), @js(old('marketplace_product_id', '')))"
            @submit="submitting = true"
        >
            @csrf
            <x-dashboard.input label="Title" name="title" :value="old('title')" required />
            <x-dashboard.textarea label="Description" name="description">{{ old('description') }}</x-dashboard.textarea>

            <div>
                <label class="block text-sm font-medium text-text-secondary mb-1">Category</label>
                <select x-model="categoryId" name="category_id" class="w-full rounded-xl border-border-default bg-elevated" required>
                    <option value="">— Select category —</option>
                    <template x-for="parent in parents" :key="parent.id">
                        <option :value="parent.id" x-text="parent.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-secondary mb-1">Product</label>
                <select x-model="productId" name="marketplace_product_id" class="w-full rounded-xl border-border-default bg-elevated" required>
                    <option value="">— Select product —</option>
                    <template x-for="prod in products" :key="prod.id">
                        <option :value="prod.id" x-text="prod.name"></option>
                    </template>
                </select>
                @error('marketplace_product_id')<p class="text-danger text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <x-dashboard.input label="Price (NGN)" type="number" name="price" min="1" :value="old('price')" required />
            <x-dashboard.button type="submit" icon="check" x-bind:disabled="submitting">Save Draft</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>

@push('scripts')
<script>
function listingProductForm(tree, initialCategoryId, initialProductId) {
    return {
        parents: tree,
        categoryId: initialCategoryId ? String(initialCategoryId) : '',
        productId: initialProductId ? String(initialProductId) : '',
        submitting: false,
        
        get products() {
            if (! this.categoryId) {
                return [];
            }
            const parent = this.parents.find(p => String(p.id) === String(this.categoryId));
            return parent ? parent.products : [];
        },
        
        init() {
            this.$watch('categoryId', (newVal, oldVal) => {
                if (newVal !== oldVal) {
                    this.productId = '';
                }
            });
        }
    };
}
</script>
@endpush
@endsection
