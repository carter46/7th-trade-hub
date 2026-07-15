import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('toastStore', (initial = []) => ({
        toasts: [],
        init() {
            (initial || []).forEach((t) => this.push(t.type, t.message));
            window.addEventListener('toast', (e) => {
                this.push(e.detail?.type || 'info', e.detail?.message || '');
            });
        },
        push(type, message) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, type, message });
            setTimeout(() => this.dismiss(id), 5000);
        },
        dismiss(id) {
            this.toasts = this.toasts.filter((t) => t.id !== id);
        },
    }));

    Alpine.data('mobileNav', () => ({
        open: false,
        init() {
            this.$watch('open', (value) => {
                document.body.style.overflow = value ? 'hidden' : '';
            });
        },
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
    }));

    Alpine.data('ecosystemSlider', () => ({
        dragging: false,
        startX: 0,
        scrollLeft: 0,
        mq: null,

        init() {
            this.mq = window.matchMedia('(min-width: 768px)');
            this.$el.addEventListener('pointerdown', (e) => this.onPointerDown(e));
            this.$el.addEventListener('pointermove', (e) => this.onPointerMove(e));
            this.$el.addEventListener('pointerup', (e) => this.onPointerUp(e));
            this.$el.addEventListener('pointercancel', (e) => this.onPointerUp(e));
            this.$el.addEventListener('pointerleave', (e) => this.onPointerUp(e));
            this.$el.addEventListener(
                'wheel',
                (e) => this.onWheel(e),
                { passive: false },
            );
        },

        isDesktop() {
            return this.mq?.matches ?? window.innerWidth >= 768;
        },

        onPointerDown(e) {
            if (this.isDesktop() || e.pointerType === 'touch') {
                return;
            }
            this.dragging = true;
            this.startX = e.clientX;
            this.scrollLeft = this.$el.scrollLeft;
            this.$el.setPointerCapture?.(e.pointerId);
        },

        onPointerMove(e) {
            if (!this.dragging || this.isDesktop()) {
                return;
            }
            e.preventDefault();
            const walk = e.clientX - this.startX;
            this.$el.scrollLeft = this.scrollLeft - walk;
        },

        onPointerUp(e) {
            if (!this.dragging) {
                return;
            }
            this.dragging = false;
            try {
                this.$el.releasePointerCapture?.(e.pointerId);
            } catch (_) {
                // ignore
            }
        },

        onWheel(e) {
            if (this.isDesktop()) {
                return;
            }
            const predominateX = Math.abs(e.deltaX) > Math.abs(e.deltaY);
            const delta = predominateX ? e.deltaX : e.deltaY;
            if (delta === 0) {
                return;
            }
            const atStart = this.$el.scrollLeft <= 0 && delta < 0;
            const atEnd =
                this.$el.scrollLeft + this.$el.clientWidth >= this.$el.scrollWidth - 1 &&
                delta > 0;
            if (atStart || atEnd) {
                return;
            }
            e.preventDefault();
            this.$el.scrollBy({ left: delta, behavior: 'auto' });
        },
    }));
});

Alpine.start();
