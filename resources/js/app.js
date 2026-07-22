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
            window.addEventListener('open-mobile-nav', () => {
                this.open = true;
            });
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

    Alpine.data('accountMenu', () => ({
        open: false,
        trigger: null,
        toggle() {
            this.trigger = this.$el.querySelector('button[aria-haspopup="menu"]');
            this.open = !this.open;
        },
        close(restoreFocus = false) {
            if (!this.open) return;
            this.open = false;
            if (restoreFocus) {
                this.$nextTick(() => this.trigger?.focus());
            }
        },
    }));

    Alpine.data('rowActions', () => ({
        open: false,
        menuStyle: { top: '0px', left: '0px', minWidth: '11rem' },
        _onScroll: null,
        _onResize: null,
        init() {
            this._onScroll = () => {
                if (this.open) this.close();
            };
            this._onResize = () => {
                if (this.open) this.position();
            };
            window.addEventListener('scroll', this._onScroll, true);
            window.addEventListener('resize', this._onResize);
            this.$watch('open', (value) => {
                if (value) {
                    this.$nextTick(() => {
                        this.position();
                        requestAnimationFrame(() => this.position());
                    });
                }
            });
        },
        destroy() {
            if (this._onScroll) {
                window.removeEventListener('scroll', this._onScroll, true);
            }
            if (this._onResize) {
                window.removeEventListener('resize', this._onResize);
            }
        },
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
        position() {
            const trigger = this.$refs.trigger;
            const menu = this.$refs.menu;
            if (!trigger || !menu) return;

            const rect = trigger.getBoundingClientRect();
            const menuRect = menu.getBoundingClientRect();
            const gap = 4;
            const pad = 8;
            const vw = window.innerWidth;
            const vh = window.innerHeight;

            let top = rect.bottom + gap;
            if (top + menuRect.height > vh - pad && rect.top - gap - menuRect.height >= pad) {
                top = rect.top - gap - menuRect.height;
            }
            top = Math.max(pad, Math.min(top, vh - menuRect.height - pad));

            let left = rect.right - menuRect.width;
            if (left < pad) left = pad;
            if (left + menuRect.width > vw - pad) {
                left = Math.max(pad, vw - menuRect.width - pad);
            }

            this.menuStyle = {
                top: `${Math.round(top)}px`,
                left: `${Math.round(left)}px`,
                minWidth: `${Math.max(176, Math.round(rect.width))}px`,
            };
        },
    }));

    Alpine.data('sidebarNav', (options = {}) => ({
        storageKey: options.storageKey || '7th.dashboard.nav',
        initiallyOpen: Array.isArray(options.initiallyOpen) ? options.initiallyOpen : [],
        destinations: Array.isArray(options.destinations) ? options.destinations : [],
        openGroupId: null,
        query: '',
        activeResult: 0,
        init() {
            const saved = this.readSavedState();
            const activeId = this.initiallyOpen[0] || null;

            if (activeId) {
                this.openGroupId = activeId;
            } else if (saved && typeof saved.openGroupId === 'string' && saved.openGroupId) {
                this.openGroupId = saved.openGroupId;
            } else if (saved && typeof saved === 'object' && !Array.isArray(saved)) {
                // Migrate legacy multi-open maps to a single accordion id.
                const legacyOpen = Object.keys(saved).find((key) => saved[key]);
                this.openGroupId = legacyOpen || null;
            }

            this.persist();
            this.$watch('query', () => {
                this.activeResult = 0;
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
                localStorage.setItem(this.storageKey, JSON.stringify({ openGroupId: this.openGroupId }));
            } catch (_) {
                // Navigation still works when storage is unavailable.
            }
        },
        isOpen(id) {
            return this.openGroupId === id;
        },
        toggleGroup(id) {
            this.openGroupId = this.isOpen(id) ? null : id;
            this.persist();
        },
        openGroup(id) {
            this.openGroupId = id;
            this.persist();
        },
        closeGroup(id) {
            if (this.openGroupId === id) {
                this.openGroupId = null;
                this.persist();
            }
        },
        clearSearch() {
            this.query = '';
            this.activeResult = 0;
        },
        filteredDestinations() {
            const term = this.query.trim().toLowerCase();
            if (!term) return [];

            return this.destinations.filter((item) => {
                const haystack = [item.label, item.group, ...(item.keywords || [])]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase();

                return haystack.includes(term);
            });
        },
        moveResult(delta) {
            const results = this.filteredDestinations();
            if (!results.length) return;
            const next = this.activeResult + delta;
            this.activeResult = (next + results.length) % results.length;
        },
        openActiveResult() {
            const results = this.filteredDestinations();
            const item = results[this.activeResult];
            if (!item?.url) return;
            window.location.href = item.url;
        },
    }));

    Alpine.data('notificationMenu', () => ({
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
    }));

    /**
     * Foundation for a future Ctrl+K command palette.
     * Reuses the same destination registry shape as sidebar search.
     */
    Alpine.data('commandPalette', (options = {}) => ({
        open: false,
        query: '',
        activeResult: 0,
        destinations: Array.isArray(options.destinations) ? options.destinations : [],
        init() {
            window.addEventListener('keydown', (event) => {
                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                    event.preventDefault();
                    this.open = !this.open;
                    if (this.open) {
                        this.query = '';
                        this.activeResult = 0;
                    }
                }
            });
            this.$watch('open', (value) => {
                if (value) {
                    this.$nextTick(() => this.$refs.paletteInput?.focus());
                }
            });
        },
        filtered() {
            const term = this.query.trim().toLowerCase();
            if (!term) return this.destinations.slice(0, 12);

            return this.destinations.filter((item) => {
                const haystack = [item.label, item.group, ...(item.keywords || [])]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase();

                return haystack.includes(term);
            }).slice(0, 12);
        },
        close() {
            this.open = false;
        },
    }));

    Alpine.data('themeSwitcher', (initialPreference = 'system') => ({
        preference: initialPreference,
        saving: false,
        init() {
            window.addEventListener('dashboard-theme-changed', (event) => {
                const next = event.detail?.preference;
                if (typeof next === 'string' && next !== '') {
                    this.preference = next;
                }
            });
        },
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

    Alpine.data('dashboardAjaxTabs', (initialActive = null) => ({
        activeId: initialActive,
        panelSelector: '#dashboard-tab-panel',
        loading: false,
        _abort: null,
        _seq: 0,
        _onPopState: null,
        async navigate(event, href, id) {
            const panel = document.querySelector(this.panelSelector);
            if (!panel) {
                window.location.href = href;
                return;
            }
            const seq = ++this._seq;
            if (this._abort) {
                this._abort.abort();
            }
            this._abort = new AbortController();
            this.loading = true;
            panel.setAttribute('aria-busy', 'true');
            try {
                const res = await fetch(href, {
                    headers: {
                        'X-Dashboard-Tab': '1',
                        Accept: 'text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    signal: this._abort.signal,
                });
                if (seq !== this._seq) {
                    return;
                }
                if (!res.ok) {
                    window.location.href = href;
                    return;
                }
                const html = await res.text();
                if (seq !== this._seq) {
                    return;
                }
                if (window.Alpine?.destroyTree) {
                    window.Alpine.destroyTree(panel);
                }
                panel.innerHTML = html;
                this.activeId = id;
                history.pushState({ dashboardTab: id }, '', href);
                if (window.Alpine?.initTree) {
                    window.Alpine.initTree(panel);
                }
                window.dispatchEvent(new CustomEvent('dashboard-tab-navigated', { detail: { id, href } }));
            } catch (e) {
                if (e?.name === 'AbortError') {
                    return;
                }
                window.location.href = href;
            } finally {
                if (seq === this._seq) {
                    this.loading = false;
                    panel.removeAttribute('aria-busy');
                }
            }
        },
        init() {
            this._onPopState = () => {
                window.location.reload();
            };
            window.addEventListener('popstate', this._onPopState);
        },
        destroy() {
            if (this._abort) {
                this._abort.abort();
            }
            if (this._onPopState) {
                window.removeEventListener('popstate', this._onPopState);
            }
        },
    }));

    Alpine.data('mediaPicker', (opts = {}) => ({
        name: opts.name,
        multiple: !!opts.multiple,
        selectedId: opts.selectedId ?? null,
        selectedIds: opts.selectedIds ?? [],
        previewUrl: opts.previewUrl ?? null,
        previews: opts.previews ?? [],
        get previewItems() {
            return this.previews;
        },
        openLibrary() {
            window.DashboardMedia.open({
                multiple: this.multiple,
                onSelect: (items) => {
                    const list = Array.isArray(items) ? items : [items];
                    if (this.multiple) {
                        list.forEach((item) => {
                            if (!this.selectedIds.includes(item.id)) {
                                this.selectedIds.push(item.id);
                                this.previews.push({ id: item.id, url: item.thumbnail_url || item.url });
                            }
                        });
                    } else {
                        const item = list[0];
                        if (item) {
                            this.selectedId = item.id;
                            this.previewUrl = item.thumbnail_url || item.url;
                        }
                    }
                },
            });
        },
        clear() {
            this.selectedId = null;
            this.selectedIds = [];
            this.previewUrl = null;
            this.previews = [];
        },
        removeAt(index) {
            this.selectedIds.splice(index, 1);
            this.previews.splice(index, 1);
        },
        move(index, delta) {
            const next = index + delta;
            if (next < 0 || next >= this.selectedIds.length) {
                return;
            }
            const ids = this.selectedIds.splice(index, 1)[0];
            const preview = this.previews.splice(index, 1)[0];
            this.selectedIds.splice(next, 0, ids);
            this.previews.splice(next, 0, preview);
        },
    }));

    Alpine.data('mediaLibraryModal', (opts = {}) => ({
        isOpen: false,
        multiple: false,
        type: 'image',
        q: '',
        assets: [],
        selected: [],
        loading: false,
        uploading: false,
        error: null,
        page: 1,
        lastPage: 1,
        jsonUrl: opts.jsonUrl,
        storeUrl: opts.storeUrl,
        csrf: opts.csrf,
        previouslyFocused: null,
        previousRootOverflow: '',
        _abort: null,
        _seq: 0,

        openModal(detail = {}) {
            this.multiple = !!detail.multiple;
            this.type = detail.type || 'image';
            this.selected = [];
            this.error = null;
            this.page = 1;
            this.previouslyFocused = document.activeElement;
            this.previousRootOverflow = document.documentElement.style.overflow;
            document.documentElement.style.overflow = 'hidden';
            this.isOpen = true;
            this.fetchAssets();
            this.$nextTick(() => {
                this.focusableElements()[0]?.focus();
            });
        },

        closeModal() {
            this.isOpen = false;
            document.documentElement.style.overflow = this.previousRootOverflow;
            if (window.DashboardMedia) {
                window.DashboardMedia._callback = null;
            }
            this.$nextTick(() => {
                this.previouslyFocused?.focus?.();
            });
        },

        focusableElements() {
            const panel = this.$refs.mediaPanel;
            if (!panel) return [];
            return [...panel.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
            )].filter((el) => el.offsetParent !== null);
        },

        trapFocus(event) {
            if (!this.isOpen || event.key !== 'Tab') return;
            const focusable = this.focusableElements();
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

        isSelected(id) {
            return this.selected.some((item) => item.id === id);
        },

        toggle(asset) {
            if (this.multiple) {
                const idx = this.selected.findIndex((item) => item.id === asset.id);
                if (idx >= 0) {
                    this.selected.splice(idx, 1);
                } else {
                    this.selected.push(asset);
                }
                return;
            }
            this.selected = [asset];
        },

        confirm() {
            if (!this.selected.length || !window.DashboardMedia) {
                return;
            }
            window.DashboardMedia.select(this.multiple ? [...this.selected] : this.selected[0]);
        },

        async fetchAssets(append = false) {
            const seq = ++this._seq;
            if (this._abort) {
                this._abort.abort();
            }
            this._abort = new AbortController();
            this.loading = true;
            this.error = null;
            try {
                const url = new URL(this.jsonUrl, window.location.origin);
                if (this.q.trim()) {
                    url.searchParams.set('q', this.q.trim());
                }
                if (this.type) {
                    url.searchParams.set('type', this.type);
                }
                url.searchParams.set('page', String(append ? this.page : 1));
                if (!append) {
                    this.page = 1;
                }
                const res = await fetch(url.toString(), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    signal: this._abort.signal,
                });
                if (seq !== this._seq) {
                    return;
                }
                if (!res.ok) {
                    throw new Error('Failed to load media');
                }
                const payload = await res.json();
                const rows = payload.data || payload.assets || [];
                this.assets = append ? [...this.assets, ...rows] : rows;
                this.lastPage = payload.meta?.last_page || 1;
                this.page = payload.meta?.current_page || this.page;
            } catch (e) {
                if (e?.name === 'AbortError') {
                    return;
                }
                this.error = e.message || 'Failed to load media';
                if (!append) {
                    this.assets = [];
                }
            } finally {
                if (seq === this._seq) {
                    this.loading = false;
                }
            }
        },

        loadMore() {
            if (this.page >= this.lastPage || this.loading) {
                return;
            }
            this.page += 1;
            this.fetchAssets(true);
        },

        async upload(event) {
            const files = Array.from(event.target.files || []);
            event.target.value = '';
            if (!files.length) {
                return;
            }
            this.uploading = true;
            this.error = null;
            try {
                const form = new FormData();
                files.forEach((file) => form.append('files[]', file));
                const res = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrf || document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: form,
                    credentials: 'same-origin',
                });
                const payload = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const first = payload.message
                        || (payload.errors && Object.values(payload.errors).flat()[0])
                        || 'Upload failed';
                    throw new Error(first);
                }
                const uploaded = payload.assets || payload.data || [];
                if (uploaded.length) {
                    this.assets = [...uploaded, ...this.assets.filter((a) => !uploaded.some((u) => u.id === a.id))];
                    if (this.multiple) {
                        uploaded.forEach((asset) => {
                            if (!this.isSelected(asset.id)) {
                                this.selected.push(asset);
                            }
                        });
                    } else if (uploaded[0]) {
                        this.selected = [uploaded[0]];
                    }
                }
            } catch (e) {
                this.error = e.message || 'Upload failed';
            } finally {
                this.uploading = false;
            }
        },
    }));
});

window.DashboardMedia = {
    _callback: null,
    open({ multiple = false, type = 'image', onSelect } = {}) {
        this._callback = onSelect;
        window.dispatchEvent(new CustomEvent('open-media-library', {
            detail: { multiple, type },
        }));
    },
    select(items) {
        if (typeof this._callback === 'function') {
            this._callback(items);
        }
        this._callback = null;
        window.dispatchEvent(new CustomEvent('close-media-library'));
    },
};

Alpine.start();
