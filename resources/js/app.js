import './bootstrap';
import './dashboard-theme';

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
        previouslyFocused: null,
        previousRootOverflow: '',
        init() {
            this.$watch('open', (value) => {
                if (value) {
                    this.previouslyFocused = document.activeElement;
                    this.previousRootOverflow = document.documentElement.style.overflow;
                    document.documentElement.style.overflow = 'hidden';
                    this.$nextTick(() => {
                        this.drawerFocusableElements()[0]?.focus();
                    });
                } else {
                    document.documentElement.style.overflow = this.previousRootOverflow;
                    document.body.style.overflow = '';
                    this.$nextTick(() => {
                        this.previouslyFocused?.focus?.();
                    });
                }
            });
        },
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
        drawerFocusableElements() {
            const drawer = this.$refs.mobileDrawer;
            if (!drawer) return [];

            return [...drawer.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
            )].filter((element) => element.offsetParent !== null);
        },
        trapFocus(event) {
            if (!this.open || window.innerWidth >= 1024 || event.key !== 'Tab') return;

            const focusable = this.drawerFocusableElements();
            if (!focusable.length) return;

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    }));

    Alpine.data('sidebarNav', (options = {}) => ({
        storageKey: options.storageKey || '7th.dashboard.nav',
        initiallyOpen: Array.isArray(options.initiallyOpen) ? options.initiallyOpen : [],
        openGroups: {},
        init() {
            const saved = this.readSavedState();

            if (saved && typeof saved === 'object' && !Array.isArray(saved)) {
                this.openGroups = { ...saved };
            }

            // The current page's parent is always expanded on first render.
            this.initiallyOpen.forEach((id) => {
                this.openGroups[id] = true;
            });
        },
        readSavedState() {
            try {
                return JSON.parse(localStorage.getItem(this.storageKey) || 'null');
            } catch (_) {
                return null;
            }
        },
        persist() {
            try {
                localStorage.setItem(this.storageKey, JSON.stringify(this.openGroups));
            } catch (_) {
                // Navigation still works when storage is unavailable.
            }
        },
        isOpen(id) {
            return Boolean(this.openGroups[id]);
        },
        toggleGroup(id) {
            this.openGroups = { ...this.openGroups, [id]: !this.isOpen(id) };
            this.persist();
        },
        openGroup(id) {
            if (this.isOpen(id)) return;
            this.openGroups = { ...this.openGroups, [id]: true };
            this.persist();
        },
        closeGroup(id) {
            if (!this.isOpen(id)) return;
            this.openGroups = { ...this.openGroups, [id]: false };
            this.persist();
        },
    }));

    Alpine.data('themeSwitcher', (initialPreference = 'system') => ({
        preference: initialPreference,
        saving: false,
        async choose(next) {
            if (this.saving || this.preference === next) return;
            this.saving = true;
            const previous = this.preference;
            this.preference = next;
            const result = await window.DashboardTheme.setPreference(next);
            if (!result?.ok) {
                this.preference = previous;
            }
            this.saving = false;
        },
    }));

    Alpine.data('listingCategoryForm', (parents = [], parentId = 0, categoryId = 0, filtersOpen = false) => ({
        parents,
        parentId,
        categoryId,
        filtersOpen: Boolean(filtersOpen),
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

    Alpine.data('marketplaceBrowse', (config = {}) => ({
        parents: config.parents || [],
        parentId: Number(config.parentId || 0),
        categoryId: Number(config.categoryId || 0),
        q: config.q || '',
        sort: config.sort || 'newest',
        featured: Boolean(config.featured),
        filtersOpen: Boolean(config.filtersOpen),
        loading: false,
        suggestions: [],
        keywords: [],
        showSuggest: false,
        suggestTimer: null,
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
        queryParams() {
            const params = new URLSearchParams();
            if (this.q) params.set('q', this.q);
            if (Number(this.parentId) > 0) params.set('parent', String(this.parentId));
            if (Number(this.categoryId) > 0) params.set('category', String(this.categoryId));
            if (this.sort && this.sort !== 'newest') params.set('sort', this.sort);
            if (this.featured) params.set('featured', '1');
            return params;
        },
        async applyFilters(event) {
            if (event) event.preventDefault();
            this.showSuggest = false;
            this.loading = true;
            try {
                const params = this.queryParams();
                params.set('ajax', '1');
                const url = `${config.indexUrl}?${params.toString()}`;
                const res = await fetch(url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) throw new Error('Filter failed');
                const data = await res.json();
                const target = document.getElementById('marketplace-results');
                if (target && data.html) {
                    target.innerHTML = data.html;
                }
                const clean = new URL(window.location.href);
                clean.search = this.queryParams().toString();
                window.history.replaceState({}, '', clean.pathname + (clean.search ? `?${clean.search}` : ''));
            } catch (e) {
                const form = this.$refs.filterForm;
                if (form) form.submit();
            } finally {
                this.loading = false;
            }
        },
        onSearchInput() {
            clearTimeout(this.suggestTimer);
            const term = (this.q || '').trim();
            if (term.length < 2) {
                this.suggestions = [];
                this.keywords = [];
                this.showSuggest = false;
                return;
            }
            this.suggestTimer = setTimeout(() => this.fetchSuggestions(), 250);
        },
        async fetchSuggestions() {
            try {
                const url = `${config.suggestUrl}?q=${encodeURIComponent(this.q.trim())}`;
                const res = await fetch(url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) return;
                const data = await res.json();
                this.suggestions = data.suggestions || [];
                this.keywords = data.keywords || [];
                this.showSuggest = this.suggestions.length > 0 || this.keywords.length > 0;
            } catch (e) {
                this.showSuggest = false;
            }
        },
        pickKeyword(word) {
            this.q = word;
            this.showSuggest = false;
            this.applyFilters();
        },
        hideSuggestSoon() {
            setTimeout(() => {
                this.showSuggest = false;
            }, 180);
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
