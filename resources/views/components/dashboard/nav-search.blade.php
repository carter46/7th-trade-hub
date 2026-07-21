<div class="shrink-0 px-1">
    <label class="sr-only" for="dashboard-nav-search">Search navigation</label>
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-text-muted">
            <x-dashboard.icon name="search" class="h-4 w-4" />
        </span>
        <input
            id="dashboard-nav-search"
            type="search"
            x-model="query"
            @keydown.escape.prevent="clearSearch()"
            @keydown.arrow-down.prevent="moveResult(1)"
            @keydown.arrow-up.prevent="moveResult(-1)"
            @keydown.enter.prevent="openActiveResult()"
            placeholder="Search..."
            autocomplete="off"
            class="w-full rounded-xl border border-border-default bg-surface py-2.5 pl-9 pr-3 text-sm text-text-primary placeholder:text-text-muted focus-ring"
            data-dashboard-nav-search
        />
    </div>
</div>
