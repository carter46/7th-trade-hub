@php
    $menu = config('menus.admin', []);
@endphp
<aside
    id="sidebar"
    class="w-64 flex flex-col border-r border-border-default bg-sidebar p-4 gap-4 fixed inset-y-0 left-0 z-50 transform transition-transform duration-200 motion-reduce:transition-none lg:static lg:translate-x-0 lg:h-full lg:min-h-0"
    :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    :aria-hidden="(!open && window.innerWidth < 1024).toString()"
>
    <div class="flex items-center justify-between lg:hidden mb-2 shrink-0">
        <p class="text-sm font-semibold text-text-primary">Menu</p>
        <button type="button" class="p-2 rounded-lg focus-ring text-text-secondary" @click="close()" aria-label="Close menu">
            <x-ui.icon name="x" class="w-5 h-5" />
        </button>
    </div>
    <div class="flex flex-col gap-1 flex-1 min-h-0 overflow-y-auto">
        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-text-muted mb-2">Main Menu</p>
        @foreach ($menu as $item)
            @php
                $url = \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#';
                $active = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '*');
            @endphp
            <x-ui.nav-link :href="$url" :icon="$item['icon']" :active="$active" @click="close()">
                {{ $item['label'] }}
            </x-ui.nav-link>
        @endforeach
    </div>
    <div class="mt-auto pt-6 flex flex-col gap-1 border-t border-border-default shrink-0">
        <x-ui.nav-link :href="route('profile.edit')" icon="settings" @click="close()">Settings</x-ui.nav-link>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="ghost" icon="logout" class="w-full !justify-start">
                Sign Out
            </x-ui.button>
        </form>
    </div>
</aside>
