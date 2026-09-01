{{-- Authenticated dashboards must not use the marketing offline service worker. --}}
<script>
(function () {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    navigator.serviceWorker.getRegistrations().then(function (registrations) {
        registrations.forEach(function (registration) {
            registration.unregister();
        });
    });

    if ('caches' in window) {
        caches.keys().then(function (keys) {
            keys.forEach(function (key) {
                if (String(key).indexOf('offline-cache') === 0) {
                    caches.delete(key);
                }
            });
        });
    }
})();
</script>
