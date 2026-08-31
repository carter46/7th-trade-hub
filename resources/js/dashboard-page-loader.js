const LOADER_ID = 'dashboard-page-loader';
const LOADER_COLOR = { r: 11, g: 106, b: 57 };

function buildLoaderMarkup() {
    return `
      <div class="dashboard-page-loader__veil" aria-hidden="true"></div>
      <div class="dashboard-page-loader__content">
        <div class="dashboard-page-loader__stage" aria-hidden="true">
          <div class="dashboard-page-loader__ring"></div>
          <canvas class="dashboard-page-loader__canvas" width="176" height="176"></canvas>
        </div>
      </div>`;
}

function ensureLoaderElement() {
    let root = document.getElementById(LOADER_ID);
    if (root) {
        return root;
    }
    if (!document.body) {
        return null;
    }

    root = document.createElement('div');
    root.id = LOADER_ID;
    root.className = 'dashboard-page-loader';
    root.setAttribute('role', 'status');
    root.setAttribute('aria-live', 'polite');
    root.setAttribute('aria-busy', 'true');
    root.setAttribute('aria-label', 'Loading');
    root.innerHTML = buildLoaderMarkup();
    document.body.insertBefore(root, document.body.firstChild);

    return root;
}

let animationFrameId = 0;
let animationActive = false;

function startLoaderAnimation(root) {
    const canvas = root.querySelector('.dashboard-page-loader__canvas');
    const ctx = canvas?.getContext?.('2d');
    if (!ctx || animationActive) {
        return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const size = 176;
    const center = size / 2;
    const radius = 52;
    const trail = [];
    const trailMax = 28;
    let angle = -Math.PI / 2;
    let startedAt = performance.now();
    animationActive = true;

    function speedAt(elapsedMs) {
        const cycle = 4200;
        const phase = ((elapsedMs % cycle) / cycle) * Math.PI * 2;
        return 0.55 + 1.3 * (0.5 - 0.5 * Math.cos(phase));
    }

    function drawFrame(now) {
        if (!animationActive) {
            return;
        }

        const elapsed = now - startedAt;
        angle += speedAt(elapsed) * 0.042;

        const x = center + Math.cos(angle) * radius;
        const y = center + Math.sin(angle) * radius;
        trail.push({ x, y });
        if (trail.length > trailMax) {
            trail.shift();
        }

        ctx.clearRect(0, 0, size, size);

        ctx.beginPath();
        ctx.arc(center, center, radius, 0, Math.PI * 2);
        ctx.strokeStyle = 'rgba(11, 106, 57, 0.08)';
        ctx.lineWidth = 1;
        ctx.stroke();

        for (let i = 0; i < trail.length - 1; i += 1) {
            const t = (i + 1) / trail.length;
            const point = trail[i];
            const alpha = 0.04 + t * 0.28;
            const dotRadius = 1.1 + t * 2.1;
            ctx.beginPath();
            ctx.arc(point.x, point.y, dotRadius, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(${LOADER_COLOR.r}, ${LOADER_COLOR.g}, ${LOADER_COLOR.b}, ${alpha})`;
            ctx.fill();
        }

        const lead = trail[trail.length - 1] || { x, y };
        ctx.beginPath();
        ctx.arc(lead.x, lead.y, 3.4, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(${LOADER_COLOR.r}, ${LOADER_COLOR.g}, ${LOADER_COLOR.b}, 0.88)`;
        ctx.fill();

        ctx.beginPath();
        ctx.arc(lead.x, lead.y, 6.5, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(${LOADER_COLOR.r}, ${LOADER_COLOR.g}, ${LOADER_COLOR.b}, 0.12)`;
        ctx.fill();

        animationFrameId = requestAnimationFrame(drawFrame);
    }

    if (reduceMotion) {
        ctx.beginPath();
        ctx.arc(center, center, radius, 0, Math.PI * 2);
        ctx.strokeStyle = 'rgba(11, 106, 57, 0.12)';
        ctx.lineWidth = 1;
        ctx.stroke();
        ctx.beginPath();
        ctx.arc(center + radius, center, 3.4, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(11, 106, 57, 0.75)';
        ctx.fill();
        return;
    }

    animationFrameId = requestAnimationFrame(drawFrame);
}

export function showDashboardPageLoader() {
    const root = ensureLoaderElement();
    if (!root) {
        return;
    }

    root.classList.remove('is-leaving');
    root.style.opacity = '1';
    root.style.visibility = 'visible';
    root.setAttribute('aria-busy', 'true');
    document.body.classList.add('dashboard-page-loading');
    startLoaderAnimation(root);
}

function isInternalNavLink(anchor) {
    if (!anchor || anchor.tagName !== 'A') {
        return false;
    }
    if (anchor.hasAttribute('data-no-page-loader')) {
        return false;
    }
    if (anchor.target && anchor.target.toLowerCase() === '_blank') {
        return false;
    }
    if (anchor.hasAttribute('download')) {
        return false;
    }

    const href = anchor.getAttribute('href');
    if (!href || href.charAt(0) === '#') {
        return false;
    }
    if (/^(mailto:|tel:|sms:|javascript:)/i.test(href)) {
        return false;
    }

    let url;
    try {
        url = new URL(href, window.location.href);
    } catch {
        return false;
    }

    if (url.origin !== window.location.origin) {
        return false;
    }

    return !(
        url.pathname === window.location.pathname
        && url.search === window.location.search
        && url.hash
    );
}

function shouldShowLoaderForForm(form) {
    if (!(form instanceof HTMLFormElement)) {
        return false;
    }
    if (form.dataset.noPageLoader !== undefined) {
        return false;
    }
    if (form.dataset.ajaxForm !== undefined) {
        return false;
    }
    if ((form.method || 'get').toLowerCase() === 'dialog') {
        return false;
    }

    return true;
}

export function initDashboardPageLoader() {
    if (document.body?.dataset?.dashboardShell !== 'user') {
        return;
    }

    window.showDashboardPageLoader = showDashboardPageLoader;

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0) {
            return;
        }
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const anchor = event.target?.closest?.('a');
        if (!isInternalNavLink(anchor)) {
            return;
        }

        showDashboardPageLoader();
    }, true);

    document.addEventListener('submit', (event) => {
        if (event.defaultPrevented || !shouldShowLoaderForForm(event.target)) {
            return;
        }

        showDashboardPageLoader();
    }, true);

    window.addEventListener('beforeunload', () => {
        showDashboardPageLoader();
    });
}
