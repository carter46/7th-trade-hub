<aside
    id="sidebar"
    x-ref="mobileDrawer"
    class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[88vw] flex-col gap-4 border-r border-border-default bg-sidebar p-4 shadow-panel transform transition-transform duration-200 motion-reduce:transition-none lg:static lg:h-full lg:min-h-0 lg:w-64 lg:max-w-none lg:translate-x-0 lg:shadow-none"
    :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    :aria-hidden="(!open && window.innerWidth < 1024).toString()"
    @keydown.tab="trapFocus($event)"
>
    <div class="flex items-center justify-between lg:hidden mb-2 shrink-0">
        <div>
            <p class="text-sm font-semibold text-text-primary">Administration</p>
            <p class="text-xs text-text-muted">Navigation</p>
        </div>
        <button type="button" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg focus-ring text-text-secondary hover:bg-muted/50 hover:text-text-primary" @click="close()" aria-label="Close menu">
            <x-ui.icon name="x" class="w-5 h-5" />
        </button>
    </div>

    <x-dashboard.nav role="admin" :user="auth()->user()" label="Admin navigation" />

    <div class="mt-auto flex shrink-0 flex-col gap-1 border-t border-border-default pt-3">
        <x-ui.nav-link :href="route('profile.edit')" icon="user" :active="request()->routeIs('profile.*')" @click="close()">Account</x-ui.nav-link>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-dashboard.button type="submit" variant="ghost" icon="logout" class="w-full !justify-start !text-danger hover:!bg-danger/10 hover:!text-danger">
                Sign Out
            </x-dashboard.button>
        </form>
        <div class="mt-2 lg:hidden" data-mobile-theme-switcher>
            <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-wider text-text-muted">Appearance</p>
            <x-dashboard.theme-switcher />
        </div>
    </div>
</aside>
