<?php

namespace OpenSoutheners\LaravelCompanionApps\Generators;

class ServiceWorkerGenerator
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(protected array $options = [])
    {
        /** @var array<string, mixed> $config */
        $config = config('companion.pwa.service_worker', []);

        $this->options = array_merge($config, $this->options);
    }

    public function generate(): string
    {
        $cacheName = $this->json($this->options['cache_name'] ?? 'companion-app-v1');
        $offlineUrl = $this->json($this->options['offline_url'] ?? '/offline');
        $precache = $this->json($this->options['precache'] ?? []);
        $assetPatterns = $this->json($this->options['asset_patterns'] ?? []);

        return <<<JS
const CACHE_NAME = {$cacheName};
const OFFLINE_URL = {$offlineUrl};
const PRECACHE_URLS = {$precache};
const ASSET_PATTERNS = {$assetPatterns};

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => Promise.allSettled(
                PRECACHE_URLS.map(url =>
                    fetch(url, { cache: 'reload' })
                        .then(response => response.ok ? cache.put(url, response) : null)
                        .catch(() => null)
                )
            ))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.map(key => key === CACHE_NAME ? null : caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const request = event.request;

    if (request.method !== 'GET' || request.headers.has('authorization')) {
        return;
    }

    const url = new URL(request.url);

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then(response => {
                    if (! shouldCacheResponse(response)) {
                        return response;
                    }

                    const copy = response.clone();

                    event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.put(request, copy)));

                    return response;
                })
                .catch(() => caches.match(OFFLINE_URL))
        );

        return;
    }

    if (ASSET_PATTERNS.some(pattern => url.pathname.startsWith(pattern))) {
        event.respondWith(
            caches.match(request)
                .then(cached => cached || fetch(request).then(response => {
                    if (! shouldCacheResponse(response)) {
                        return response;
                    }

                    const copy = response.clone();

                    event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.put(request, copy)));

                    return response;
                }))
        );
    }
});

function shouldCacheResponse(response) {
    if (! response || ! response.ok || response.type === 'opaque') {
        return false;
    }

    const cacheControl = response.headers.get('cache-control') || '';

    return ! cacheControl.includes('private') && ! cacheControl.includes('no-store');
}
JS;
    }

    protected function json(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: 'null';
    }
}
