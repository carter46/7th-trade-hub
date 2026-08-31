"use strict";

const CACHE_NAME = "offline-cache-v2";
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

    if (event.request.mode === "navigate") {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    if (response && response.ok) {
                        return response;
                    }

                    return caches.match(OFFLINE_URL);
                })
                .catch(() => caches.match(OFFLINE_URL)),
        );

        return;
    }

    event.respondWith(
        fetch(event.request)
            .catch(() => caches.match(event.request)),
    );
});
