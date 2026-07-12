@php
    $menu = config('menus.admin', []);
@endphp
<aside class="w-full lg:w-64 flex flex-col border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-background-dark p-4 gap-4">
    <div class="flex flex-col gap-1">
        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">Main Menu</p>
        @foreach ($menu as $item)
            @php
                $url = \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#';
                $active = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '*');
            @endphp
            <a href="{{ $url }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ $active ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors' }}">
                <span class="material-symbols-outlined text-[22px]">{{ $item['icon'] }}</span>
                <span class="text-sm font-medium">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
    <div class="mt-auto pt-6 flex flex-col gap-1 border-t border-slate-100 dark:border-slate-800">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            <span class="material-symbols-outlined text-[22px]">settings</span>
            <span class="text-sm font-medium">Settings</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="contents">
            @csrf
            <button type="submit" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors w-full text-left">
                <span class="material-symbols-outlined text-[22px]">logout</span>
                <span class="text-sm font-medium">Sign Out</span>
            </button>
        </form>
    </div>
</aside>
