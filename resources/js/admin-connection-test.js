export function registerAdminConnectionTest(Alpine) {
    Alpine.data('adminConnectionTest', () => ({
        testing: false,
        status: '',
        ok: null,
        async run(event) {
            event.preventDefault();
            if (this.testing) {
                return;
            }

            const form = event.target.closest('form');
            if (!form) {
                return;
            }

            this.testing = true;
            this.status = '';
            this.ok = null;

            try {
                const res = await fetch(form.action, {
                    method: form.method || 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: new FormData(form),
                });

                const data = await res.json().catch(() => ({}));
                this.ok = Boolean(data.ok);
                this.status = data.message
                    || (this.ok ? 'Connection successful.' : 'Connection failed.');

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: this.ok ? 'success' : 'danger',
                        message: this.status,
                    },
                }));
            } catch {
                this.ok = false;
                this.status = 'Request failed. Try again.';
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'danger', message: this.status },
                }));
            } finally {
                this.testing = false;
            }
        },
    }));
}
