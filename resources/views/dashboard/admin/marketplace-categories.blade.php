@extends('layouts.dashboard-admin')
@section('title', 'Marketplace Categories')
@section('content')
<x-layout.page title="Marketplace Categories" width="content">
    <x-ui.card class="mb-6">
        <form method="POST" action="{{ route('admin.marketplace-categories.store') }}" class="grid md:grid-cols-3 gap-3 items-end">
            @csrf
            <x-ui.input label="Name" name="name" required />
            <x-ui.select label="Parent (optional)" name="parent_id">
                <option value="">Top level</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.button type="submit">Add</x-ui.button>
        </form>
    </x-ui.card>
    <div class="space-y-4">
        @foreach($parents as $parent)
            <x-ui.card>
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-bold">{{ $parent->name }}</h3>
                    <form method="POST" action="{{ route('admin.marketplace-categories.toggle', $parent) }}">
                        @csrf
                        <x-ui.button type="submit" size="xs" :variant="$parent->is_active ? 'secondary' : 'success'">
                            {{ $parent->is_active ? 'Deactivate' : 'Activate' }}
                        </x-ui.button>
                    </form>
                </div>
                <ul class="text-sm text-text-secondary space-y-2">
                    @forelse($parent->children as $child)
                        <li class="flex justify-between items-center gap-2">
                            <span>• {{ $child->name }}</span>
                            <form method="POST" action="{{ route('admin.marketplace-categories.toggle', $child) }}">
                                @csrf
                                <x-ui.button type="submit" size="xs" :variant="$child->is_active ? 'secondary' : 'success'">
                                    {{ $child->is_active ? 'Off' : 'On' }}
                                </x-ui.button>
                            </form>
                        </li>
                    @empty
                        <li>No subcategories</li>
                    @endforelse
                </ul>
            </x-ui.card>
        @endforeach
    </div>
</x-layout.page>
@endsection
