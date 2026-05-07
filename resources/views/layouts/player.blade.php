<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        @stack('meta')
        
        <title>{{ config('app.name', 'Laravel') }}</title>

        <style>
            html,
            body {
                width: 100%;
                height: 100%;
                margin: 0;
                overflow: hidden;
                background: #000000;
            }
        </style>

        @stack('styles')

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">

        <script>
            // Register the Cross-Origin Isolation service worker.
            // This is a JavaScript-side fallback that enables SharedArrayBuffer
            // when the server-side COOP/COEP headers are not yet active (e.g. nginx
            // proxy hasn't been reconfigured, or a CDN strips the headers).
            // On the FIRST visit it reloads the page once to activate the worker.
            // On all subsequent visits the page is cross-origin isolated immediately.
            (function () {
                if (!window.isSecureContext) return;          // HTTP-only env; skip
                if (window.crossOriginIsolated) return;       // Server headers already set
                if (!('serviceWorker' in navigator)) return;

                navigator.serviceWorker.register('/coi-serviceworker.js').then(function (reg) {
                    if (!navigator.serviceWorker.controller) {
                        // Worker just installed for the first time — reload to activate.
                        reg.installing && reg.installing.addEventListener('statechange', function (e) {
                            if (e.target.state === 'activated') window.location.reload();
                        });
                    }
                }).catch(function (err) {
                    console.warn('[COI-SW] Service worker registration failed:', err);
                });
            })();
        </script>

        <script>
            window.VintagePlayerFetch = (input, init = {}) => window.fetch(input, init);
        </script>

        @vite(['resources/js/player.js'])

        @livewireStyles
    </head>
    <body>
        
        {{ $slot }}

        @stack('scripts')
        
        @livewireScripts

    </body>
</html>
