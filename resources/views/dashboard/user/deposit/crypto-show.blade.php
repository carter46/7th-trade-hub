@extends('layouts.dashboard-user')

@section('title', $sell->tracking_code ?: 'Sell #'.$sell->id)

@section('content')
@php
    $payload = $initialPayload ?? [];
@endphp
<x-layout.page
    title="{{ $sell->tracking_code ?: 'Sell #'.$sell->id }}"
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Sell Crypto', route('dashboard.crypto-sell.index')],
        [$sell->tracking_code ?: '#'.$sell->id, null],
    ]"
>
    <div
        class="space-y-4"
        x-data="cryptoSellTracker(@js($payload), @js($statusUrl), @js($qrUrl), @js($supportUrl), @js(route('dashboard.crypto-sell.refresh', $sell)), @js(route('dashboard.wallet')), @js(route('dashboard.withdrawal.create')))"
        x-init="init()"
    >
        <div
            x-show="connectionMessage"
            x-cloak
            class="rounded-xl px-4 py-2 text-sm"
            :class="connectionLost ? 'border border-warning/40 bg-warning/10 text-warning' : 'border border-emerald-500/30 bg-emerald-500/10 text-emerald-700'"
            x-text="connectionMessage"
        ></div>

        <div
            x-show="leavePromptOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @keydown.escape.window="stay()"
        >
            <div class="w-full max-w-md rounded-2xl border border-border-default bg-elevated p-5 shadow-panel">
                <p class="text-base font-semibold text-text-primary">Leave this order?</p>
                <p class="mt-2 text-sm text-text-secondary">You haven't completed your crypto deposit yet.</p>
                <div class="mt-4 flex flex-wrap justify-end gap-2">
                    <button type="button" class="rounded-xl border border-border-default px-4 py-2 text-sm font-medium" @click="stay()">Stay</button>
                    <button type="button" class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white" @click="confirmLeave()">Leave</button>
                </div>
            </div>
        </div>

        {{-- Approved --}}
        <template x-if="stage === 'approved'">
            <x-dashboard.card>
                <div class="text-center py-4 space-y-3">
                    <p class="text-4xl text-emerald-500">✓</p>
                    <h2 class="text-xl font-semibold text-text-primary">Wallet Successfully Funded</h2>
                    <p class="font-display text-3xl font-bold text-text-primary">₦<span x-text="fmt(payload.credit_ngn)"></span></p>
                    <p class="text-sm text-text-secondary">Available Balance ₦<span x-text="fmt(payload.wallet_available_ngn)"></span></p>
                    <p class="text-xs text-text-muted">Available for withdrawal</p>
                    <div class="flex flex-wrap justify-center gap-2 pt-2">
                        <a :href="withdrawUrl" class="inline-flex items-center rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white">Withdraw Now</a>
                        <a :href="walletUrl" class="inline-flex items-center rounded-xl border border-border-default px-4 py-2.5 text-sm font-medium text-text-primary">Go to Wallet</a>
                    </div>
                </div>
            </x-dashboard.card>
        </template>

        {{-- Rejected --}}
        <template x-if="stage === 'rejected'">
            <x-dashboard.card>
                <h2 class="text-lg font-semibold text-danger">Deposit Rejected</h2>
                <p class="mt-2 text-sm text-text-secondary" x-text="payload.admin_notes || 'This order was rejected by admin.'"></p>
                <a :href="supportUrl" class="mt-4 inline-flex text-sm font-medium text-primary underline-offset-2 hover:underline">Contact Support</a>
            </x-dashboard.card>
        </template>

        {{-- Expired --}}
        <template x-if="stage === 'expired' || stage === 'cancelled'">
            <x-dashboard.card>
                <h2 class="text-lg font-semibold text-warning">Quote expired</h2>
                <p class="mt-2 text-sm text-text-secondary">Generate a new quote — the previous rate is no longer valid.</p>
                <form method="POST" :action="refreshUrl" class="mt-4">
                    @csrf
                    <x-dashboard.button type="submit" variant="secondary" size="sm">New Quote</x-dashboard.button>
                </form>
            </x-dashboard.card>
        </template>

        {{-- Active deposit UI --}}
        <template x-if="['waiting_deposit','deposit_detected','awaiting_admin','underpaid','overpaid'].includes(stage)">
            <div class="space-y-4">
                {{-- Hero address first --}}
                <x-dashboard.card>
                    <h2 class="text-lg font-semibold text-text-primary mb-1">Send <span x-text="payload.coin"></span></h2>
                    <p class="text-xs text-warning mb-4">⚠ Only send <span x-text="payload.coin"></span> on the <span x-text="payload.network"></span> network.</p>

                    <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                        <img :src="qrUrl" alt="Deposit QR" class="h-52 w-52 rounded-xl border border-border-default bg-white p-2" width="208" height="208">
                        <div class="min-w-0 flex-1 w-full space-y-3">
                            <div>
                                <p class="text-xs text-text-muted mb-1">Wallet Address</p>
                                <p class="break-all font-mono text-sm text-text-primary select-all" x-text="payload.platform_address"></p>
                            </div>
                            <button
                                type="button"
                                class="rounded-xl border border-border-default px-3 py-2 text-sm font-medium text-text-primary hover:bg-muted/40"
                                @click="copyAddress()"
                                x-text="copied ? 'Copied' : 'Copy'"
                            ></button>
                            <p class="text-sm text-text-secondary">Network: <span class="font-medium text-text-primary" x-text="payload.network"></span></p>
                        </div>
                    </div>
                </x-dashboard.card>

                {{-- Quote details --}}
                <x-dashboard.card>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-text-muted">USD Value</dt><dd class="font-semibold">$<span x-text="fmt(payload.amount_usd)"></span></dd></div>
                        <div><dt class="text-text-muted">Send</dt><dd class="font-semibold"><span x-text="payload.amount_crypto"></span> <span x-text="payload.coin"></span></dd></div>
                        <div><dt class="text-text-muted">You receive</dt><dd class="font-semibold">₦<span x-text="fmt(payload.expected_ngn)"></span></dd></div>
                        <div><dt class="text-text-muted">Locked Rate</dt><dd class="font-semibold">₦<span x-text="fmt(payload.quoted_rate_ngn)"></span> /$</dd></div>
                        <div><dt class="text-text-muted">Confirmations Required</dt><dd class="font-semibold" x-text="payload.required_confirmations"></dd></div>
                        <div><dt class="text-text-muted">Status</dt><dd class="font-semibold capitalize" x-text="stageLabel()"></dd></div>
                    </dl>
                </x-dashboard.card>

                {{-- Countdown OR confirmation panel --}}
                <x-dashboard.card>
                    <template x-if="payload.show_countdown">
                        <div class="text-center py-2">
                            <p class="text-sm text-text-muted">Quote expires in</p>
                            <p class="mt-1 font-display text-4xl font-bold tabular-nums text-text-primary" x-text="countdownLabel"></p>
                            <p class="mt-3 text-sm text-text-secondary">Monitoring blockchain… Waiting for transaction…</p>
                        </div>
                    </template>
                    <template x-if="payload.show_confirmation_panel">
                        <div class="space-y-3">
                            <p class="text-sm font-medium text-text-primary">Blockchain Status</p>
                            <p class="text-sm text-text-secondary">Incoming transaction detected</p>
                            <p class="text-lg font-semibold text-text-primary" x-show="payload.amount_received">
                                <span x-text="payload.amount_received"></span> <span x-text="payload.coin"></span>
                            </p>
                            <p class="text-sm text-text-muted" x-show="stage === 'deposit_detected'">Waiting for confirmations…</p>
                            <p class="text-sm text-emerald-600" x-show="stage === 'awaiting_admin'">Deposit confirmed · Waiting for admin approval</p>

                            <div class="space-y-1">
                                <div class="h-2.5 w-full overflow-hidden rounded-full bg-muted">
                                    <div class="h-full rounded-full bg-primary transition-all duration-500" :style="'width:' + (payload.conf_progress * 100) + '%'"></div>
                                </div>
                                <p class="text-sm text-text-secondary">
                                    <span x-text="payload.confirmations_observed"></span> of <span x-text="payload.required_confirmations"></span> confirmations
                                </p>
                                <p class="text-xs text-text-muted">Average: 10–20 minutes</p>
                            </div>

                            <p class="text-sm text-text-secondary" x-show="stage === 'awaiting_admin'">
                                Your cryptocurrency has been confirmed. Your NGN wallet will be credited after manual verification. This usually takes only a few minutes.
                            </p>
                        </div>
                    </template>
                </x-dashboard.card>

                {{-- Underpaid --}}
                <template x-if="stage === 'underpaid'">
                    <x-dashboard.card>
                        <h3 class="font-semibold text-warning">Deposit received — shortfall</h3>
                        <dl class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2 text-sm">
                            <div><dt class="text-text-muted">Amount received</dt><dd class="font-medium" x-text="payload.amount_received + ' ' + payload.coin"></dd></div>
                            <div><dt class="text-text-muted">Expected</dt><dd class="font-medium" x-text="payload.amount_crypto + ' ' + payload.coin"></dd></div>
                            <div><dt class="text-text-muted">Shortfall</dt><dd class="font-medium text-warning" x-text="(payload.shortfall || 0) + ' ' + payload.coin"></dd></div>
                        </dl>
                        <p class="mt-2 text-sm text-text-secondary">Waiting for remaining deposit. You can keep sending to the same address.</p>
                        <a :href="supportUrl" class="mt-3 inline-block text-sm text-primary underline-offset-2 hover:underline">Contact Support</a>
                    </x-dashboard.card>
                </template>

                {{-- Overpaid --}}
                <template x-if="stage === 'overpaid'">
                    <x-dashboard.card>
                        <h3 class="font-semibold text-warning">More crypto received than expected</h3>
                        <p class="mt-2 text-sm text-text-secondary">Status: Awaiting admin review. Payout amount will be confirmed by admin.</p>
                    </x-dashboard.card>
                </template>

                {{-- TX details --}}
                <template x-if="payload.tx_hash">
                    <x-dashboard.card>
                        <h3 class="text-sm font-semibold text-text-primary mb-3">Transaction</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div class="sm:col-span-2"><dt class="text-text-muted">Hash</dt><dd class="font-mono text-xs break-all" x-text="payload.tx_hash"></dd></div>
                            <div><dt class="text-text-muted">Amount</dt><dd x-text="(payload.amount_received || payload.amount_crypto) + ' ' + payload.coin"></dd></div>
                            <div><dt class="text-text-muted">Network</dt><dd x-text="payload.network"></dd></div>
                            <div><dt class="text-text-muted">Detected</dt><dd x-text="payload.detected_at ? new Date(payload.detected_at).toLocaleString() : '—'"></dd></div>
                            <div>
                                <template x-if="payload.explorer_url">
                                    <a :href="payload.explorer_url" target="_blank" rel="noopener" class="text-primary text-sm underline">Open in explorer</a>
                                </template>
                            </div>
                        </dl>
                    </x-dashboard.card>
                </template>

                <details class="rounded-xl border border-border-subtle px-4 py-3" x-show="stage === 'waiting_deposit'">
                    <summary class="cursor-pointer text-sm font-medium text-text-primary">Have a TX hash?</summary>
                    <form method="POST" action="{{ route('dashboard.crypto-sell.tx', $sell) }}" class="mt-3 space-y-3">
                        @csrf
                        <x-dashboard.input name="tx_hash" label="Transaction hash" :value="old('tx_hash', $sell->tx_hash)" required />
                        <x-dashboard.button type="submit" size="sm">Submit hash</x-dashboard.button>
                    </form>
                </details>

                <form method="POST" action="{{ route('dashboard.crypto-sell.cancel', $sell) }}" x-show="stage === 'waiting_deposit'" x-cloak @submit="leaveGuard = false">
                    @csrf
                    <x-dashboard.button type="submit" variant="secondary" size="sm">Cancel order</x-dashboard.button>
                </form>
            </div>
        </template>

        {{-- Timeline --}}
        <x-dashboard.card>
            <h3 class="text-sm font-semibold text-text-primary mb-4">Order timeline</h3>
            <ol class="space-y-3">
                <template x-for="step in timelineSteps()" :key="step.key">
                    <li class="flex items-start gap-3 text-sm">
                        <span
                            class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                            :class="{
                                'bg-emerald-500/15 text-emerald-600': step.state === 'done',
                                'bg-primary/15 text-primary animate-pulse': step.state === 'current',
                                'bg-muted text-text-muted': step.state === 'pending'
                            }"
                            x-text="step.state === 'done' ? '✓' : (step.state === 'current' ? '▶' : '○')"
                        ></span>
                        <span :class="step.state === 'pending' ? 'text-text-muted' : 'text-text-primary font-medium'" x-text="step.label"></span>
                    </li>
                </template>
            </ol>
        </x-dashboard.card>
    </div>
</x-layout.page>

<script>
window.cryptoSellTracker = function (initial, statusUrl, qrUrl, supportUrl, refreshUrl, walletUrl, withdrawUrl) {
    return {
        payload: initial,
        statusUrl,
        qrUrl,
        supportUrl,
        refreshUrl,
        walletUrl,
        withdrawUrl,
        tick: null,
        pollTimer: null,
        copied: false,
        connectionLost: false,
        connectionMessage: '',
        leaveGuard: true,
        leavePromptOpen: false,
        pendingLeaveHref: null,
        _leaveHandler: null,
        _clickHandler: null,
        get stage() { return this.payload.stage || 'waiting_deposit'; },
        get countdownLabel() {
            const s = Math.max(0, Number(this.payload.seconds_remaining || 0));
            const m = Math.floor(s / 60);
            const r = s % 60;
            return String(m).padStart(2, '0') + ':' + String(r).padStart(2, '0');
        },
        fmt(n) {
            return Number(n || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        stageLabel() {
            const map = {
                waiting_deposit: 'Waiting for deposit',
                deposit_detected: 'Deposit detected',
                awaiting_admin: 'Awaiting admin',
                underpaid: 'Underpaid',
                overpaid: 'Overpaid review',
                approved: 'Approved',
                rejected: 'Rejected',
                expired: 'Expired',
                cancelled: 'Cancelled',
            };
            return map[this.stage] || this.stage;
        },
        timelineSteps() {
            const order = [
                { key: 'quote', label: 'Quote Created' },
                { key: 'deposit', label: 'Deposit Detected' },
                { key: 'confs', label: 'Confirmations Complete' },
                { key: 'admin', label: this.stage === 'approved' ? 'Approved' : 'Admin Reviewing' },
                { key: 'wallet', label: 'Wallet Funded' },
                { key: 'withdraw', label: 'Withdrawal Complete' },
            ];
            let current = 0;
            if (['deposit_detected', 'underpaid', 'overpaid'].includes(this.stage)) current = 1;
            if (this.stage === 'awaiting_admin') current = 3;
            if (this.stage === 'approved') current = 4;
            if (this.stage === 'rejected') current = 3;
            if (this.stage === 'expired' || this.stage === 'cancelled') current = 0;
            if (this.stage === 'deposit_detected' && (this.payload.conf_progress || 0) >= 1) current = 2;

            return order.map((step, i) => {
                let state = 'pending';
                if (this.stage === 'approved') {
                    state = i <= 4 ? 'done' : 'pending';
                } else if (i < current) state = 'done';
                else if (i === current) state = 'current';
                if (step.key === 'withdraw') state = 'pending';
                return { ...step, state };
            });
        },
        init() {
            this.startCountdown();
            this.schedulePoll();
            this._leaveHandler = (e) => {
                if (this.leaveGuard && this.stage === 'waiting_deposit') {
                    e.preventDefault();
                    e.returnValue = '';
                }
            };
            window.addEventListener('beforeunload', this._leaveHandler);
            this._clickHandler = (e) => {
                if (! this.leaveGuard || this.stage !== 'waiting_deposit') return;
                const a = e.target.closest('a[href]');
                if (! a) return;
                const href = a.getAttribute('href');
                if (! href || href.startsWith('#') || href.startsWith('javascript:')) return;
                if (a.target === '_blank') return;
                e.preventDefault();
                this.pendingLeaveHref = a.href;
                this.leavePromptOpen = true;
            };
            document.addEventListener('click', this._clickHandler, true);
        },
        stay() {
            this.leavePromptOpen = false;
            this.pendingLeaveHref = null;
        },
        confirmLeave() {
            this.leaveGuard = false;
            const href = this.pendingLeaveHref;
            this.leavePromptOpen = false;
            this.pendingLeaveHref = null;
            if (href) window.location.href = href;
        },
        startCountdown() {
            if (this.tick) clearInterval(this.tick);
            this.tick = setInterval(() => {
                if (! this.payload.show_countdown) return;
                if ((this.payload.seconds_remaining || 0) > 0) {
                    this.payload.seconds_remaining -= 1;
                }
                if ((this.payload.seconds_remaining || 0) <= 0) {
                    this.poll();
                }
            }, 1000);
        },
        schedulePoll() {
            if (this.pollTimer) clearTimeout(this.pollTimer);
            const ms = Number(this.payload.poll_interval_ms || 0);
            if (! ms || this.payload.is_terminal) return;
            this.pollTimer = setTimeout(() => this.poll(), ms);
        },
        async poll() {
            try {
                const res = await fetch(this.statusUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (! res.ok) throw new Error('bad status');
                const data = await res.json();
                const wasLost = this.connectionLost;
                this.payload = data;
                this.connectionLost = false;
                if (wasLost) {
                    this.connectionMessage = 'Connected';
                    setTimeout(() => { if (! this.connectionLost) this.connectionMessage = ''; }, 2000);
                }
                if (this.stage !== 'waiting_deposit') this.leaveGuard = false;
                this.schedulePoll();
            } catch (e) {
                this.connectionLost = true;
                this.connectionMessage = 'Connection lost · Trying again…';
                this.pollTimer = setTimeout(() => this.poll(), 5000);
            }
        },
        async copyAddress() {
            const copyFn = window.copyToClipboard;
            const ok = typeof copyFn === 'function'
                ? await copyFn(this.payload.platform_address || '')
                : false;
            if (ok) {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 1500);
                return;
            }
            alert(window.copyFailedMessage?.() || 'Unable to copy.');
        },
    };
};
</script>
@endsection
