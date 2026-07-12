@extends('layouts.dashboard-admin')
@section('title', 'User Management')
@section('content')
<h1 class="text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">User Management</h1>
<p class="text-slate-500 dark:text-slate-400 text-base mt-1">Manage platform users and roles.</p>

<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden mt-6">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50 dark:bg-background-dark/50 text-slate-500 text-xs uppercase font-bold">
                <tr>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Username</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Verified</th>
                    <th class="px-6 py-4">Joined</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($users ?? [] as $u)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">{{ $u->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $u->username ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $u->email }}</td>
                        <td class="px-6 py-4">
                            @php $roles = $u->roles->pluck('name'); @endphp
                            @if($roles->isNotEmpty())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $roles->contains('admin') ? 'bg-primary/20 text-primary' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                    {{ $roles->join(', ') }}
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($u->email_verified_at)
                                <span class="text-emerald-600 dark:text-emerald-500 text-sm">Yes</span>
                            @else
                                <span class="text-slate-400 text-sm">No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-500 text-xs">{{ $u->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500 text-sm">No users yet. Run <code class="text-slate-600">php artisan db:seed</code> to load demo data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($users) && $users->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
