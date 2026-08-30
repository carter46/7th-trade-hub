/**
 * Fade-up reveal when elements enter the viewport.
 */
function initScrollReveal() {
    const nodes = document.querySelectorAll('[data-reveal="fade-up"]');
    if (!nodes.length) {
        return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion || !('IntersectionObserver' in window)) {
        nodes.forEach((el) => el.classList.add('is-revealed'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                entry.target.classList.add('is-revealed');
                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.2, rootMargin: '0px 0px -8% 0px' },
    );

    nodes.forEach((el) => observer.observe(el));
}

document.addEventListener('DOMContentLoaded', initScrollReveal);
