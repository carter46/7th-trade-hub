<main
    id="main-content"
    class="dashboard-scroll-main min-h-0 min-w-0 flex-1 overflow-y-auto overscroll-y-contain bg-surface p-4 lg:p-8"
    x-data="pullToRefresh"
    @touchstart.passive="onTouchStart($event)"
    @touchmove.passive="onTouchMove($event)"
    @touchend="onTouchEnd()"
    @touchcancel="onTouchEnd()"
>
    <div
        x-cloak
        x-show="showIndicator"
        class="dashboard-pull-refresh pointer-events-none flex items-end justify-center overflow-hidden text-text-muted"
        :style="{ height: indicatorHeight + 'px' }"
        aria-hidden="true"
    >
        <div
            class="mb-2 flex items-center gap-2 text-xs font-medium transition-opacity"
            :class="readyToRefresh || refreshing ? 'opacity-100' : 'opacity-70'"
        >
            <svg
                class="h-4 w-4 shrink-0 text-primary"
                :class="{ 'animate-spin': refreshing }"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v4m0 0-2-2m2 2 2-2M5 12a7 7 0 0 1 12-2" />
            </svg>
            <span x-text="refreshing ? 'Refreshing…' : (readyToRefresh ? 'Release to refresh' : 'Pull to refresh')"></span>
        </div>
    </div>

    {{ $slot }}
</main>
