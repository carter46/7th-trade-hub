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
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
    }));
});

Alpine.start();
