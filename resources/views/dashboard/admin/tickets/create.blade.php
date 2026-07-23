@extends('layouts.dashboard-admin')

@section('title', 'Open Support Ticket')

@section('content')
<x-layout.page
    title="Open ticket on behalf of user"
    subtitle="Create a support ticket assigned to you."
    width="md"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Support Tickets', route('admin.tickets')],
        ['Open ticket', null],
    ]"
>
    <x-dashboard.card variant="solid">
        <form method="POST" action="{{ route('admin.tickets.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-text-secondary mb-1">User</label>
                <select name="user_id" class="w-full rounded-xl border-border-default bg-elevated" required>
                    <option value="">Select user...</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-secondary mb-1">Category</label>
                <select name="category" class="w-full rounded-xl border-border-default bg-elevated" required>
                    @foreach (\App\Models\SupportTicket::CATEGORIES as $cat)
                        <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ ucfirst(str_replace('_', ' ', $cat)) }}</option>
                    @endforeach
                </select>
            </div>
            <x-dashboard.input name="subject" label="Subject" :value="old('subject')" required />
            <x-dashboard.textarea name="body" label="Message" :rows="5" required>{{ old('body') }}</x-dashboard.textarea>
            <x-dashboard.select name="priority" label="Priority">
                <option value="normal">Normal</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </x-dashboard.select>
            <x-dashboard.button type="submit" variant="primary">Create ticket</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
