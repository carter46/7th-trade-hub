<div class="space-y-4 p-1">
    <h3 class="text-lg font-semibold {{ ($dashboard ?? true) ? 'text-text-primary' : 'text-slate-900' }}">View demo</h3>
    <p class="text-sm {{ ($dashboard ?? true) ? 'text-text-secondary' : 'text-slate-600' }}">
        Open the independent demo site without a password. Launches in a new tab.
    </p>
    <div class="flex flex-col gap-2">
        @if ($canDemoUser)
            <form method="POST" action="{{ route('dashboard.services.demo-launch', [$product, 'user']) }}" target="_blank" rel="noopener">
                @csrf
                @if ($dashboard ?? true)
                    <x-dashboard.button type="submit" class="w-full" variant="secondary">Login as User</x-dashboard.button>
                @else
                    <x-ui.button type="submit" variant="secondary" class="w-full">Login as User</x-ui.button>
                @endif
            </form>
        @endif
        @if ($canDemoAdmin)
            <form method="POST" action="{{ route('dashboard.services.demo-launch', [$product, 'admin']) }}" target="_blank" rel="noopener">
                @csrf
                @if ($dashboard ?? true)
                    <x-dashboard.button type="submit" class="w-full">Login as Admin</x-dashboard.button>
                @else
                    <x-ui.button type="submit" variant="primary" class="w-full">Login as Admin</x-ui.button>
                @endif
            </form>
        @endif
    </div>
    @if (! empty($closeAction))
        <button type="button" class="w-full text-sm text-slate-500 hover:text-slate-800" @click="{{ $closeAction }}">Cancel</button>
    @endif
</div>
