export function registerManualBankPayment(Alpine) {
    Alpine.data('manualBankPayment', (config) => ({
        secondsRemaining: config.secondsRemaining,
        phase: config.paymentExpired ? 'failed' : 'active',
        showProofForm: false,
        proofSubmitted: config.proofSubmitted,
        submitted: false,
        submitting: false,
        restarting: false,
        copied: false,
        cancelModalOpen: false,
        cancelMessage: '',
        statusMessage: '',
        paymentSession: config.paymentSession,
        maxSessions: config.maxSessions,
        expireUrl: config.expireUrl,
        restartUrl: config.restartUrl,
        submitUrl: config.submitUrl,
        dashboardUrl: config.dashboardUrl,
        csrfToken: config.csrfToken,
        accountNumber: config.accountNumber,
        timerId: null,
        init() {
            if (this.proofSubmitted) {
                return;
            }
            if (this.phase === 'active') {
                this.startTimer();
            }
        },
        get countdownLabel() {
            const total = Math.max(0, this.secondsRemaining);
            const minutes = Math.floor(total / 60);
            const seconds = total % 60;
            return `${minutes}:${String(seconds).padStart(2, '0')}`;
        },
        startTimer() {
            if (this.timerId) {
                clearInterval(this.timerId);
            }
            this.timerId = setInterval(() => {
                if (this.proofSubmitted || this.submitted || this.phase !== 'active') {
                    return;
                }
                if (this.secondsRemaining > 0) {
                    this.secondsRemaining -= 1;
                    return;
                }
                this.handleExpiry();
            }, 1000);
        },
        async handleExpiry() {
            if (this.phase !== 'active') {
                return;
            }
            try {
                const res = await fetch(this.expireUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });
                const data = await res.json().catch(() => ({}));
                if (data.status === 'cancelled') {
                    this.phase = 'cancelled';
                    this.cancelMessage = data.message || 'Your order is being cancelled because payment was not completed in time.';
                    this.cancelModalOpen = true;
                    return;
                }
                this.phase = 'failed';
                this.showProofForm = false;
            } catch (e) {
                this.phase = 'failed';
                this.showProofForm = false;
            }
        },
        async restartPayment() {
            if (this.restarting) {
                return;
            }
            this.restarting = true;
            try {
                const res = await fetch(this.restartUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.ok) {
                    throw new Error(data.message || 'Unable to restart payment.');
                }
                this.phase = 'active';
                this.showProofForm = false;
                this.secondsRemaining = Number(data.seconds_remaining || 600);
                this.paymentSession = Number(data.session || this.paymentSession);
                this.startTimer();
            } catch (e) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'danger', message: e.message || 'Unable to restart payment.' },
                }));
            } finally {
                this.restarting = false;
            }
        },
        async copyAccountNumber() {
            if (!this.accountNumber) {
                return;
            }
            try {
                await navigator.clipboard.writeText(this.accountNumber);
                this.copied = true;
                setTimeout(() => {
                    this.copied = false;
                }, 2000);
            } catch (e) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'danger', message: 'Unable to copy account number.' },
                }));
            }
        },
        async submitProof() {
            if (this.submitting) {
                return;
            }
            const file = this.$refs.proofInput?.files?.[0];
            if (!file) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'danger', message: 'Payment proof is required.' },
                }));
                return;
            }
            this.submitting = true;
            const formData = new FormData();
            formData.append('proof', file);
            formData.append('_token', this.csrfToken);
            try {
                const res = await fetch(this.submitUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.ok) {
                    throw new Error(data.message || 'Unable to submit payment proof.');
                }
                await new Promise((resolve) => setTimeout(resolve, 5000));
                this.submitted = true;
                this.phase = 'submitted';
                this.statusMessage = data.message;
                if (this.timerId) {
                    clearInterval(this.timerId);
                }
            } catch (e) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'danger', message: e.message || 'Unable to submit payment proof.' },
                }));
            } finally {
                this.submitting = false;
            }
        },
        goToDashboard() {
            window.location.href = this.dashboardUrl;
        },
    }));
}
