@extends('layouts.dashboard-admin')
@section('title', 'Marketplace Categories')
@section('content')
<x-layout.page title="Marketplace Categories" width="content">
    <x-dashboard.card class="mb-6">
        <form method="POST" action="{{ route('admin.marketplace-categories.store') }}" class="grid md:grid-cols-3 gap-3 items-end">
            @csrf
            <x-dashboard.input label="Name" name="name" required />
            <x-dashboard.select label="Parent (optional)" name="parent_id">
                <option value="">Top level</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                @endforeach
            </x-dashboard.select>
            <x-dashboard.button type="submit">Add</x-dashboard.button>
        </form>
    </x-dashboard.card>
    <div class="space-y-4">
        @foreach($parents as $parent)
            <x-dashboard.card>
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-bold">{{ $parent->name }}</h3>
                    <form method="POST" action="{{ route('admin.marketplace-categories.toggle', $parent) }}">
                        @csrf
                        <x-dashboard.button type="submit" size="xs" :variant="$parent->is_active ? 'secondary' : 'success'">
                            {{ $parent->is_active ? 'Deactivate' : 'Activate' }}
                        </x-dashboard.button>
                    </form>
                </div>
                <ul class="text-sm text-text-secondary space-y-2">
                    @forelse($parent->children as $child)
                        <li class="flex justify-between items-center gap-2">
                            <span>• {{ $child->name }}</span>
                            <form method="POST" action="{{ route('admin.marketplace-categories.toggle', $child) }}">
                                @csrf
                                <x-dashboard.button type="submit" size="xs" :variant="$child->is_active ? 'secondary' : 'success'">
                                    {{ $child->is_active ? 'Off' : 'On' }}
                                </x-dashboard.button>
                            </form>
                        </li>
                    @empty
                        <li>No subcategories</li>
                    @endforelse
                </ul>
            </x-dashboard.card>
        @endforeach
    </div>
</x-layout.page>
@endsection
