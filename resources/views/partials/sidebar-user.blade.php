@php
    $menu = config('menus.user', []);
@endphp
<aside class="w-64 bg-slate-900 border-r border-slate-800 flex-shrink-0 hidden lg:flex flex-col" id="sidebar">
    <div class="p-6">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center font-bold text-white">7</div>
            <span class="text-xl font-bold tracking-tight">Trade Hub</span>
        </a>
    </div>
    <nav class="flex-1 px-4 space-y-1 overflow-y-auto mt-4" data-purpose="primary-navigation">
        @foreach ($menu as $item)
            @php
                $url = \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#';
                $active = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '*');
            @endphp
            <a href="{{ $url }}" class="{{ $active ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                <span class="material-symbols-outlined text-[22px]">{{ $item['icon'] }}</span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
    <div class="p-4 border-t border-slate-800">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
            <span class="material-symbols-outlined text-[22px]">settings</span>
            <span>Settings</span>
        </a>
    </div>
</aside>
