"use strict";

/**
 * Marketing-site service worker only.
 * Does NOT intercept HTML navigations — avoids offline.html loops on Laravel apps.
 * Dashboard/admin layouts unregister this worker on load.
 */
const CACHE_NAME = "offline-cache-v5";
const OFFLINE_URL = "/offline.html";

const filesToCache = [OFFLINE_URL];

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

self.addEventListener("fetch", (event) => {
    if (event.request.method !== "GET") {
        return;
    }

    // Never intercept document navigations — browser handles them directly.
    if (event.request.mode === "navigate") {
        return;
    }

    const url = new URL(event.request.url);

    // Only cache static public assets for marketing pages.
    if (!url.pathname.startsWith("/build/") && !url.pathname.startsWith("/images/")) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                if (response && response.ok) {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
                }

                return response;
            })
            .catch(() => caches.match(event.request)),
    );
});
