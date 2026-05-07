<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Add Cross-Origin Isolation headers required for SharedArrayBuffer.
 * SharedArrayBuffer is needed by EmulatorJS threaded WASM cores (SNES, N64, etc.).
 *
 * COOP prevents other origins from getting a reference to this window.
 * COEP (credentialless) blocks subresource requests from sending credentials
 * cross-origin, which is safe for CDN-hosted emulator assets.
 */
class CrossOriginIsolation
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Embedder-Policy', 'credentialless');

        return $response;
    }
}
