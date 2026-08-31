/**
 * Pull-to-refresh for dashboard main scroll pane (nested overflow-y-auto).
 */
export function registerPullToRefresh(Alpine) {
    Alpine.data('pullToRefresh', () => ({
        startY: 0,
        pullDistance: 0,
        refreshing: false,
        threshold: 72,
        maxPull: 112,
        tracking: false,
        touchDevice: false,

        init() {
            this.touchDevice = window.matchMedia('(pointer: coarse)').matches
                || 'ontouchstart' in window;

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                this.threshold = 48;
            }
        },

        onTouchStart(event) {
            if (!this.touchDevice || this.refreshing) {
                return;
            }

            if (this.isBlocked()) {
                return;
            }

            if (this.$el.scrollTop > 0) {
                return;
            }

            this.tracking = true;
            this.startY = event.touches[0].clientY;
            this.pullDistance = 0;
        },

        onTouchMove(event) {
            if (!this.tracking || this.refreshing) {
                return;
            }

            if (this.$el.scrollTop > 0) {
                this.resetPull();

                return;
            }

            const delta = event.touches[0].clientY - this.startY;
            if (delta <= 0) {
                this.pullDistance = 0;

                return;
            }

            this.pullDistance = Math.min(delta * 0.45, this.maxPull);
        },

        onTouchEnd() {
            if (!this.tracking || this.refreshing) {
                return;
            }

            if (this.pullDistance >= this.threshold) {
                this.refreshing = true;
                this.pullDistance = this.threshold;
                window.location.reload();

                return;
            }

            this.resetPull();
        },

        resetPull() {
            this.tracking = false;
            this.startY = 0;
            this.pullDistance = 0;
        },

        isBlocked() {
            if (window.innerWidth >= 1024) {
                return false;
            }

            const sidebar = document.getElementById('sidebar');

            return sidebar?.getAttribute('aria-hidden') === 'false';
        },

        get showIndicator() {
            return this.pullDistance > 0 || this.refreshing;
        },

        get indicatorHeight() {
            return Math.max(0, this.pullDistance);
        },

        get readyToRefresh() {
            return this.pullDistance >= this.threshold;
        },
    }));
}
