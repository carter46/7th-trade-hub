@extends('layouts.dashboard-admin')
@section('title', 'Platform Categories')
@section('content')
<x-layout.page title="Platform Categories" width="content">
    <x-ui.card class="mb-6">
        <form method="POST" action="{{ route('admin.platform-categories.store') }}" class="grid md:grid-cols-3 gap-3 items-end">
            @csrf
            <x-ui.input label="Name" name="name" required />
            <x-ui.select label="Product type" name="product_type" required>
                @foreach(\App\Enums\PlatformProductType::cases() as $type)
                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.button type="submit">Add</x-ui.button>
        </form>
    </x-ui.card>
    <x-ui.card>
        <ul class="space-y-2 text-sm">
            @foreach($categories as $category)
                <li class="flex justify-between items-center gap-3 border-b border-border-default py-2">
                    <span>{{ $category->name }} <span class="text-text-muted">({{ $category->product_type->value }})</span></span>
                    <form method="POST" action="{{ route('admin.platform-categories.toggle', $category) }}">
                        @csrf
                        <x-ui.button type="submit" size="xs" :variant="$category->is_active ? 'secondary' : 'success'">
                            {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                        </x-ui.button>
                    </form>
                </li>
            @endforeach
        </ul>
    </x-ui.card>
</x-layout.page>
@endsection
