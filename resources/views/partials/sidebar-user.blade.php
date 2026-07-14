@php
    $menu = config('menus.user', []);
@endphp
<aside
    id="sidebar"
    class="w-64 bg-elevated border-r border-border-default flex-shrink-0 flex flex-col fixed inset-y-0 left-0 z-50 transform transition-transform duration-200 lg:static lg:translate-x-0"
    :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    :aria-hidden="(!open && window.innerWidth < 1024).toString()"
>
    <div class="p-6 flex items-center justify-between gap-2">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center font-bold text-white">7</div>
            <span class="text-xl font-bold tracking-tight">Trade Hub</span>
        </a>
        <button type="button" class="lg:hidden p-2 text-text-secondary hover:text-text-primary rounded-lg focus-ring" @click="close()" aria-label="Close menu">
            <x-ui.icon name="x" class="w-5 h-5" />
        </button>
    </div>
    <nav class="flex-1 px-4 space-y-1 overflow-y-auto mt-2" data-purpose="primary-navigation">
        @foreach ($menu as $item)
            @php
                $url = \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#';
                $active = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '*');
            @endphp
            <x-ui.nav-link :href="$url" :icon="$item['icon']" :active="$active" @click="close()">
                {{ $item['label'] }}
            </x-ui.nav-link>
        @endforeach
    </nav>
    <div class="p-4 border-t border-border-default space-y-1">
        <x-ui.nav-link :href="route('profile.edit')" icon="settings" @click="close()">Settings</x-ui.nav-link>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="ghost" icon="logout" class="w-full !justify-start">
                Sign out
            </x-ui.button>
        </form>
    </div>
</aside>
