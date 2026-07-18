@extends('layouts.dashboard-admin')
@section('title', 'Platform Categories')
@section('content')
<x-layout.page title="Platform Categories" width="content">
    <x-ui.card class="mb-6">
        <form method="POST" action="{{ route('admin.platform-categories.store') }}" class="grid md:grid-cols-2 gap-3 items-end">
            @csrf
            <x-ui.input label="Name" name="name" required />
            <x-ui.select label="Product type" name="product_type" required>
                @foreach(\App\Enums\PlatformProductType::cases() as $type)
                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input label="Short description (optional)" name="short_description" />
            <x-ui.input label="Hero title (optional)" name="hero_title" />
            <x-ui.input label="Hero subtitle (optional)" name="hero_subtitle" class="md:col-span-2" />
            <x-ui.input label="Banner image path" name="banner_image" placeholder="/images/..." />
            <x-ui.input label="Card image path" name="card_image" placeholder="/images/..." />
            <x-ui.button type="submit">Add</x-ui.button>
        </form>
    </x-ui.card>
    <div class="space-y-4">
        @foreach($categories as $category)
            <x-ui.card>
                <div class="flex flex-wrap justify-between items-center gap-3 mb-3">
                    <span class="font-semibold">{{ $category->name }} <span class="text-text-muted text-sm font-normal">({{ $category->product_type->value }})</span></span>
                    <form method="POST" action="{{ route('admin.platform-categories.toggle', $category) }}">
                        @csrf
                        <x-ui.button type="submit" size="xs" :variant="$category->is_active ? 'secondary' : 'success'">
                            {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                        </x-ui.button>
                    </form>
                </div>
                <form method="POST" action="{{ route('admin.platform-categories.update', $category) }}" class="grid md:grid-cols-2 gap-3">
                    @csrf
                    @method('PUT')
                    <x-ui.input label="Short description" name="short_description" :value="$category->short_description" />
                    <x-ui.input label="Hero title" name="hero_title" :value="$category->hero_title" />
                    <x-ui.input label="Hero subtitle" name="hero_subtitle" :value="$category->hero_subtitle" class="md:col-span-2" />
                    <x-ui.input label="Banner image" name="banner_image" :value="$category->banner_image" />
                    <x-ui.input label="Card image" name="card_image" :value="$category->card_image" />
                    <div class="md:col-span-2">
                        <x-ui.button type="submit" size="sm">Update content</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        @endforeach
    </div>
</x-layout.page>
@endsection
