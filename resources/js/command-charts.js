/**
 * Shared Chart.js defaults for command-center charts.
 */
export function commandChartDefaults(theme = 'emerald-area') {
    const base = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                titleFont: { size: 11, weight: '600' },
                bodyFont: { size: 11 },
                padding: 10,
                cornerRadius: 8,
            },
        },
    };

    if (theme === 'sparkline') {
        return {
            ...base,
            plugins: { ...base.plugins, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } },
        };
    }

    if (theme === 'donut') {
        return { ...base, cutout: '72%' };
    }

    return {
        ...base,
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#94a3b8', font: { size: 10, weight: '600' }, maxRotation: 0, autoSkip: true, maxTicksLimit: 8 },
                border: { display: false },
            },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(148, 163, 184, 0.16)' },
                ticks: { color: '#94a3b8', font: { size: 10 } },
                border: { display: false },
            },
        },
    };
}

export function mountCommandCharts(root = document) {
    if (!window.Chart) return;
    root.querySelectorAll('canvas.command-chart').forEach((el) => {
        if (el._commandChart) {
            el._commandChart.destroy();
            el._commandChart = null;
        }
        let labels = [];
        let datasets = [];
        try {
            labels = JSON.parse(el.dataset.labels || '[]');
            datasets = JSON.parse(el.dataset.datasets || '[]');
        } catch (_) {
            return;
        }
        const theme = el.dataset.chartTheme || 'emerald-area';
        const options = commandChartDefaults(theme);

        let type = 'line';
        if (theme === 'donut') type = 'doughnut';
        else if (theme === 'bar') type = 'bar';

        if (theme === 'emerald-area' && datasets[0]) {
            const ctx = el.getContext('2d');
            if (ctx) {
                const h = el.parentElement?.clientHeight || 240;
                const gradient = ctx.createLinearGradient(0, 0, 0, h);
                gradient.addColorStop(0, 'rgba(16, 185, 129, 0.28)');
                gradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');
                datasets[0].backgroundColor = gradient;
                datasets[0].borderColor = datasets[0].borderColor || '#10b981';
                datasets[0].fill = true;
            }
            // AVG reference line annotation via plugin-less average dataset marker
            const nums = (datasets[0].data || []).map(Number).filter((n) => !Number.isNaN(n));
            if (nums.length) {
                const avg = nums.reduce((a, b) => a + b, 0) / nums.length;
                options.plugins.annotation = undefined;
                options.plugins.tooltip.callbacks = {
                    afterBody: () => [`AVG ₦${avg.toLocaleString(undefined, { maximumFractionDigits: 0 })}`],
                };
            }
        }

        if (theme === 'sparkline' && datasets[0]) {
            datasets[0].borderColor = el.dataset.sparkColor || datasets[0].borderColor || '#10b981';
        }

        if (theme === 'line' && datasets[0]) {
            const ctx = el.getContext('2d');
            if (ctx) {
                const h = el.parentElement?.clientHeight || 180;
                const gradient = ctx.createLinearGradient(0, 0, 0, h);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.22)');
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0.02)');
                datasets[0].backgroundColor = gradient;
                datasets[0].fill = true;
            }
        }

        el._commandChart = new window.Chart(el, {
            type,
            data: { labels, datasets },
            options,
        });
    });
}

export function bindCommandRange(root, { endpoint, onHtml } = {}) {
    const wrap = root.querySelector('[data-command-range]');
    if (!wrap || !endpoint) return;

    const select = wrap.querySelector('[data-range-select]');
    const custom = wrap.querySelector('[data-custom-range]');

    const setActive = (range) => {
        custom?.classList.toggle('hidden', range !== 'custom');
        wrap.querySelectorAll('[data-range-value]').forEach((btn) => {
            const active = btn.dataset.rangeValue === range;
            btn.classList.toggle('bg-white', active);
            btn.classList.toggle('bg-elevated', active);
            btn.classList.toggle('shadow-sm', active);
            btn.classList.toggle('text-slate-800', active);
            btn.classList.toggle('text-text-primary', active);
            btn.classList.toggle('text-slate-500', !active);
            btn.classList.toggle('text-text-muted', !active);
        });
        if (select) select.value = range;
    };

    const apply = async (range) => {
        const params = new URLSearchParams({ range, persist_range: '1' });
        if (range === 'custom') {
            params.set('from', wrap.querySelector('[data-range-from]')?.value || '');
            params.set('to', wrap.querySelector('[data-range-to]')?.value || '');
            if (!params.get('from') || !params.get('to')) {
                setActive(range);
                return;
            }
        }
        setActive(range);

        const live = document.getElementById('command-live');
        live?.classList.add('command-skeleton');

        try {
            const res = await fetch(`${endpoint}?${params}`, {
                headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest', 'X-Dashboard-Tab': '1' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const html = await res.text();
            onHtml?.(html, range);
            const url = new URL(window.location.href);
            url.searchParams.set('range', range);
            if (range === 'custom') {
                url.searchParams.set('from', params.get('from'));
                url.searchParams.set('to', params.get('to'));
            } else {
                url.searchParams.delete('from');
                url.searchParams.delete('to');
            }
            history.replaceState({}, '', url);
        } catch (_) {
            // keep current UI
        } finally {
            live?.classList.remove('command-skeleton');
        }
    };

    wrap.querySelectorAll('[data-range-value]').forEach((btn) => {
        btn.addEventListener('click', () => apply(btn.dataset.rangeValue));
    });
    wrap.querySelector('[data-range-from]')?.addEventListener('change', () => {
        if ((select?.value || 'custom') === 'custom') apply('custom');
    });
    wrap.querySelector('[data-range-to]')?.addEventListener('change', () => {
        if ((select?.value || 'custom') === 'custom') apply('custom');
    });
}
