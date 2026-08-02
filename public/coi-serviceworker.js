// Cross-Origin Isolation service worker.
// Injects COOP/COEP only for EmulatorJS / JS-DOS player documents so the
// parent play page can embed YouTube normally.
//
// Based on: https://github.com/gzuidhof/coi-serviceworker (MIT)

self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));

function needsCrossOriginIsolation(url) {
    return url.pathname.startsWith('/player/') || url.pathname.startsWith('/dosplayer/');
}

self.addEventListener('fetch', function (event) {
    if (event.request.cache === 'only-if-cached' && event.request.mode !== 'same-origin') {
        return;
    }

    let requestUrl;
    try {
        requestUrl = new URL(event.request.url);
    } catch {
        return;
    }

    // Never touch cross-origin traffic (YouTube, CDN cores loaded by the page, etc.).
    if (requestUrl.origin !== self.location.origin) {
        return;
    }

    // Parent play page / app shell must stay free of COEP or YouTube blinks out.
    if (!needsCrossOriginIsolation(requestUrl)) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(function (response) {
                if (!response || response.status === 0 || response.type === 'opaque') {
                    return response;
                }

                const headers = new Headers(response.headers);
                headers.set('Cross-Origin-Opener-Policy', 'same-origin');
                headers.set('Cross-Origin-Embedder-Policy', 'credentialless');
                headers.set('Cross-Origin-Resource-Policy', 'cross-origin');

                return new Response(response.body, {
                    status: response.status,
                    statusText: response.statusText,
                    headers,
                });
            })
            .catch(function () {
                return Response.error();
            }),
    );
});
