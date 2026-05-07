// Cross-Origin Isolation service worker.
// Intercepts every response and injects the two headers that enable
// SharedArrayBuffer (required by EmulatorJS threaded WASM cores).
//
// Based on: https://github.com/gzuidhof/coi-serviceworker (MIT)

self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));

self.addEventListener('fetch', function (event) {
    // Safari quirk: skip cache-only cross-origin requests.
    if (event.request.cache === 'only-if-cached' && event.request.mode !== 'same-origin') {
        return;
    }

    event.respondWith(
        fetch(event.request).then(function (response) {
            // Opaque (no-cors) responses cannot have their headers modified.
            if (!response || response.status === 0 || response.type === 'opaque') {
                return response;
            }

            const headers = new Headers(response.headers);
            headers.set('Cross-Origin-Opener-Policy', 'same-origin');
            headers.set('Cross-Origin-Embedder-Policy', 'credentialless');
            // Allow any cross-origin resource (CDN cores, ROMs, etc.) through COEP.
            headers.set('Cross-Origin-Resource-Policy', 'cross-origin');

            return new Response(response.body, {
                status:     response.status,
                statusText: response.statusText,
                headers,
            });
        })
    );
});
