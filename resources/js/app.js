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

    Alpine.data('listingCategoryForm', (parents = [], parentId = 0, categoryId = 0) => ({
        parents,
        parentId,
        categoryId,
        submitting: false,
        get children() {
            const parent = this.parents.find((p) => Number(p.id) === Number(this.parentId));
            return parent ? parent.children : [];
        },
        init() {
            this.$watch('parentId', () => {
                if (!this.children.some((c) => Number(c.id) === Number(this.categoryId))) {
                    this.categoryId = this.children[0]?.id || 0;
                }
            });
        },
    }));

    Alpine.data('exchangeCalc', (rates = {}) => ({
        rates,
        asset: Object.keys(rates)[0] || 'USDT',
        amount: 1,
        get receive() {
            const row = this.rates[this.asset];
            if (!row) return 0;
            return (Number(this.amount) || 0) * Number(row.sell || 0);
        },
        get receiveFormatted() {
            return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 2 }).format(this.receive);
        },
        get hint() {
            const row = this.rates[this.asset];
            if (!row) return '';
            const parts = [];
            if (row.min) parts.push('Min ' + row.min);
            if (row.max) parts.push('Max ' + row.max);
            if (row.time) parts.push(row.time);
            return parts.join(' · ');
        },
    }));

    Alpine.data('platformCheckout', (variants = [], options = {}) => ({
        variants,
        variantId: options.defaultVariantId
            ?? variants.find((v) => v.is_default)?.id
            ?? variants[0]?.id
            ?? null,
        qty: 1,
        domainMode: 'none',
        basePrice: Number(options.basePrice || 0),
        get unit() {
            const row = this.variants.find((v) => Number(v.id) === Number(this.variantId));
            if (row) return Number(row.price);
            return this.basePrice;
        },
        get total() {
            return this.unit * (Number(this.qty) || 1);
        },
        get totalFormatted() {
            return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 2 }).format(this.total);
        },
    }));

    Alpine.data('ecosystemSlider', () => ({
        dragging: false,
        startX: 0,
        scrollLeft: 0,
        mq: null,
        active: 0,
        dotCount: 1,
        didAutoStep: false,
        observer: null,

        init() {
            this.mq = window.matchMedia('(min-width: 768px)');
            this.onResize = () => this.updateDots();

            this.$nextTick(() => {
                const track = this.trackEl;
                if (!track) {
                    return;
                }
                this.updateDots();
                track.addEventListener('scroll', () => this.onScroll(), { passive: true });
                track.addEventListener('pointerdown', (e) => this.onPointerDown(e));
                track.addEventListener('pointermove', (e) => this.onPointerMove(e));
                track.addEventListener('pointerup', (e) => this.onPointerUp(e));
                track.addEventListener('pointercancel', (e) => this.onPointerUp(e));
                track.addEventListener('pointerleave', (e) => this.onPointerUp(e));
                track.addEventListener('wheel', (e) => this.onWheel(e), { passive: false });
            });

            window.addEventListener('resize', this.onResize);

            this.observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting && !this.didAutoStep && !this.isDesktop()) {
                            this.didAutoStep = true;
                            window.setTimeout(() => this.slideOneStep(), 350);
                        }
                    });
                },
                { threshold: 0.45 },
            );
            this.observer.observe(this.$el);
        },

        destroy() {
            this.observer?.disconnect();
            if (this.onResize) {
                window.removeEventListener('resize', this.onResize);
            }
        },

        get trackEl() {
            return this.$refs.track;
        },

        isDesktop() {
            return this.mq?.matches ?? window.innerWidth >= 768;
        },

        stepWidth() {
            const track = this.trackEl;
            if (!track) {
                return 0;
            }
            const card = track.querySelector(':scope > div');
            if (!card) {
                return track.clientWidth / 2;
            }
            const styles = window.getComputedStyle(track);
            const gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
            return card.getBoundingClientRect().width + gap;
        },

        updateDots() {
            const track = this.trackEl;
            if (!track || this.isDesktop()) {
                this.dotCount = 1;
                this.active = 0;
                return;
            }
            const cards = track.querySelectorAll(':scope > div').length;
            const visible = 2;
            this.dotCount = Math.max(1, cards - visible + 1);
            this.onScroll();
        },

        onScroll() {
            const track = this.trackEl;
            if (!track || this.isDesktop()) {
                return;
            }
            const step = this.stepWidth();
            if (step <= 0) {
                return;
            }
            const maxIndex = Math.max(0, this.dotCount - 1);
            this.active = Math.min(maxIndex, Math.max(0, Math.round(track.scrollLeft / step)));
        },

        goTo(index) {
            const track = this.trackEl;
            if (!track || this.isDesktop()) {
                return;
            }
            const maxIndex = Math.max(0, this.dotCount - 1);
            const next = Math.min(maxIndex, Math.max(0, index));
            track.scrollTo({ left: next * this.stepWidth(), behavior: 'smooth' });
            this.active = next;
        },

        slideOneStep() {
            if (this.isDesktop()) {
                return;
            }
            const next = Math.min(this.dotCount - 1, this.active + 1);
            if (next !== this.active) {
                this.goTo(next);
            }
        },

        onPointerDown(e) {
            if (this.isDesktop() || e.pointerType === 'touch') {
                return;
            }
            this.dragging = true;
            this.startX = e.clientX;
            this.scrollLeft = this.trackEl.scrollLeft;
            this.trackEl.setPointerCapture?.(e.pointerId);
        },

        onPointerMove(e) {
            if (!this.dragging || this.isDesktop()) {
                return;
            }
            e.preventDefault();
            const walk = e.clientX - this.startX;
            this.trackEl.scrollLeft = this.scrollLeft - walk;
        },

        onPointerUp(e) {
            if (!this.dragging) {
                return;
            }
            this.dragging = false;
            try {
                this.trackEl.releasePointerCapture?.(e.pointerId);
            } catch (_) {
                // ignore
            }
            this.onScroll();
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
            const track = this.trackEl;
            const atStart = track.scrollLeft <= 0 && delta < 0;
            const atEnd =
                track.scrollLeft + track.clientWidth >= track.scrollWidth - 1 && delta > 0;
            if (atStart || atEnd) {
                return;
            }
            e.preventDefault();
            track.scrollBy({ left: delta, behavior: 'auto' });
        },
    }));
});


Alpine.start();
