"use strict";

const CACHE_NAME = "offline-cache-v4";
const OFFLINE_URL = "/offline.html";

const filesToCache = [OFFLINE_URL];

/** Never substitute offline.html for these navigations (payments, webhooks, dashboard). */
const OFFLINE_EXEMPT_PATHS = [
    "/dashboard/",
    "/admin/",
    "/dashboard/deposit/callback",
    "/payment/callback",
    "/webhooks/",
];

function isOfflineExempt(url) {
    try {
        const path = new URL(url).pathname;

        return OFFLINE_EXEMPT_PATHS.some((prefix) => path.startsWith(prefix) || path.includes(prefix));
    } catch {
        return false;
    }
}

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(filesToCache))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }

                    return undefined;
                }),
            ))
            .then(() => self.clients.claim()),
    );
});

function offlineExemptFallback(requestUrl, label) {
    const safeUrl = String(requestUrl).replace(/"/g, '&quot;');
    const title = label || "Could not reach the server";

    return new Response(
        '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' + title + '</title></head><body style="font-family:system-ui,sans-serif;padding:2rem;text-align:center"><h1>' + title + '</h1><p>Check your connection, then try again.</p><p><a href="' + safeUrl + '">Retry this page</a></p><p><a href="/dashboard">Go to dashboard</a></p></body></html>',
        { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } },
    );
}

self.addEventListener("fetch", (event) => {
    if (event.request.method !== "GET") {
        return;
    }

    if (event.request.mode === "navigate") {
        const exempt = isOfflineExempt(event.request.url);

        event.respondWith(
            fetch(event.request)
                .then((response) => response)
                .catch(() => {
                    if (exempt) {
                        return offlineExemptFallback(event.request.url);
                    }

                    return caches.match(OFFLINE_URL);
                }),
        );

        return;
    }

    event.respondWith(
        fetch(event.request)
            .catch(() => caches.match(event.request)),
    );
});
