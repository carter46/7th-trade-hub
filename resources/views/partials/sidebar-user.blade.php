<aside
    id="sidebar"
    x-ref="mobileDrawer"
    class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[88vw] flex-shrink-0 flex-col border-r border-border-default bg-sidebar shadow-panel transform transition-transform duration-200 motion-reduce:transition-none lg:static lg:h-full lg:min-h-0 lg:w-64 lg:max-w-none lg:translate-x-0 lg:shadow-none"
    :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    :aria-hidden="(!open && window.innerWidth < 1024).toString()"
    @keydown.tab="trapFocus($event)"
>
    <div class="flex shrink-0 items-center justify-between gap-2 p-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <x-dashboard.asset key="logo" class="h-8 w-auto" alt="{{ config('app.name') }}" />
            <span class="text-xl font-bold tracking-tight text-text-primary">Trade Hub</span>
        </a>
        <button type="button" class="lg:hidden inline-flex min-h-11 min-w-11 items-center justify-center text-text-secondary hover:bg-muted/50 hover:text-text-primary rounded-lg focus-ring" @click="close()" aria-label="Close menu">
            <x-ui.icon name="x" class="w-5 h-5" />
        </button>
    </div>

    <div class="flex min-h-0 flex-1 px-4 pb-2">
        <x-dashboard.nav role="user" :user="auth()->user()" label="Member navigation" />
    </div>

    <div class="shrink-0 space-y-1 border-t border-border-default p-4">
        <x-ui.nav-link :href="route('profile.edit')" icon="settings" :active="request()->routeIs('profile.*')" @click="close()">Settings</x-ui.nav-link>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-dashboard.button type="submit" variant="ghost" icon="logout" class="w-full !justify-start !text-danger hover:!bg-danger/10 hover:!text-danger">
                Sign out
            </x-dashboard.button>
        </form>
        <div class="pt-2 lg:hidden" data-mobile-theme-switcher>
            <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-wider text-text-muted">Appearance</p>
            <x-dashboard.theme-switcher />
        </div>
    </div>
</aside>
