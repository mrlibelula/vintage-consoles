<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        @stack('meta')
        
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Universal Player Loader - Shows immediately -->
        <style>
            /* Universal loader for all games */
            #universal-game-loader {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                background: #000000 !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: center !important;
                align-items: center !important;
                z-index: 999999 !important;
                color: white !important;
                font-family: system-ui, -apple-system, sans-serif !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .universal-spinner {
                width: 60px;
                height: 60px;
                border: 4px solid rgba(255, 255, 255, 0.2);
                border-top: 4px solid #ffffff;
                border-radius: 50%;
                animation: universalSpin 1s linear infinite;
                margin-bottom: 20px;
            }

            @keyframes universalSpin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            .universal-loading-text {
                font-size: 18px;
                font-weight: 500;
                margin-bottom: 8px;
            }

            .universal-loading-subtext {
                font-size: 14px;
                opacity: 0.7;
            }

            /* Ensure no scrollbars or margins */
            body, html {
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }
        </style>

        @stack('styles')

        @livewireStyles
    </head>
    <body>
        
        <!-- Universal Game Loader - appears immediately -->
        <div id="universal-game-loader">
            <div class="universal-spinner"></div>
            <div class="universal-loading-text">Loading Game...</div>
            <div class="universal-loading-subtext">Preparing emulator...</div>
        </div>

        {{ $slot }}

        <!-- Global function to hide the universal loader -->
        <script>
            window.hideUniversalLoader = function() {
                console.log("Hiding universal game loader");
                const loader = document.getElementById('universal-game-loader');
                if (loader) {
                    loader.style.opacity = '0';
                    loader.style.transition = 'opacity 0.5s ease-out';
                    setTimeout(() => {
                        loader.style.display = 'none';
                    }, 500);
                }
            };

            // Fallback timeout to hide loader after 20 seconds
            setTimeout(function() {
                const loader = document.getElementById('universal-game-loader');
                if (loader && loader.style.display !== 'none') {
                    console.log("Universal loader fallback timeout - hiding loader");
                    window.hideUniversalLoader();
                }
            }, 20000);
        </script>

        @stack('scripts')
        
        @livewireScripts

    </body>
</html>
