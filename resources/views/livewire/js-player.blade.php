<div>
    <!-- EmulatorJS Loader Overlay - Rose themed with game info -->
    <div id="emulatorjs-loader" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: #000000;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 999999;
        color: white;
        font-family: system-ui, -apple-system, sans-serif;
        margin: 0;
        padding: 16px;
    ">
        <!-- Rose Spinner -->
        <div id="emulatorjs-spinner" style="
            width: 48px;
            height: 48px;
            border: 4px solid rgba(255, 255, 255, 0.2);
            border-top: 4px solid #e60012;
            border-radius: 50%;
            animation: emulatorjsSpin 1s linear infinite;
            margin-bottom: 16px;
        "></div>
        <!-- Loading Text -->
        <div id="emulatorjs-title" style="
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 8px;
            text-align: center;
            line-height: 1.25;
            padding: 0 8px;
            max-width: 320px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        ">Loading {{ $title }}</div>
        <div style="
            font-size: 12px;
            opacity: 0.7;
            text-align: center;
            line-height: 1.25;
            padding: 0 8px;
        ">Please wait while the ROM loads...</div>
    </div>

    <div id="game"></div>
    
    @push('styles')
    <style>
        #game {
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background: #000000;
        }

        #game iframe,
        #game canvas {
            display: block;
            max-width: 100%;
            max-height: 100%;
        }

        /* Desktop-only: hide EmulatorJS "hamburger" toggle (virtual gamepad menu toggle). */
        @media (min-width: 768px) and (pointer: fine) {
            .ejs_virtualGamepad_open {
                display: none !important;
            }
        }

        @keyframes emulatorjsSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive design for EmulatorJS loader */
        @media (min-width: 640px) {
            #emulatorjs-spinner {
                width: 64px !important;
                height: 64px !important;
                margin-bottom: 24px !important;
            }
            #emulatorjs-title {
                font-size: 20px !important;
                max-width: 384px !important;
            }
            #emulatorjs-loader div:last-child {
                font-size: 14px !important;
            }
        }

        @media (min-width: 768px) {
            #emulatorjs-loader {
                padding: 32px !important;
            }
            #emulatorjs-title {
                font-size: 24px !important;
                max-width: 448px !important;
            }
        }
    </style>
    @endpush
    
    @push('scripts')
    <script>
        // Hide EmulatorJS loader function
        function hideEmulatorJSLoader() {
            const loader = document.getElementById('emulatorjs-loader');
            if (loader) {
                loader.style.opacity = '0';
                loader.style.transition = 'opacity 0.5s ease-out';
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 500);
            }
        }

        EJS_player = "#game";
        EJS_core = "{{ $short_name }}";
        EJS_gameName = "{{ $title }}";
        // EJS_AdUrl = "https://libe.dev";
        EJS_color = "#e60012";
        EJS_startOnLoaded = true;
        EJS_pathtodata = "https://cdn.jsdelivr.net/gh/EmulatorJS/EmulatorJS@4.0.7/data/";
        EJS_gameUrl = "{{ $game_url }}";
        EJS_gameID = "{{ $game_id }}";
        EJS_oldCores = true;
        window.VintageSaveStateConfig = @json($save_state_config);
        let emulatorJsLoaderStarted = false;

        const loadEmulatorJsScript = () => {
            if (emulatorJsLoaderStarted) {
                return;
            }

            emulatorJsLoaderStarted = true;
            const script = document.createElement('script');
            script.src = "https://cdn.jsdelivr.net/gh/EmulatorJS/EmulatorJS@4.0.7/data/loader.js";
            document.body.appendChild(script);
        };

        const startEmulatorJsSaveStates = () => {
            if (!window.VintageSaveStateManager || window.VintageEmulatorSaveStates) {
                return;
            }

            window.VintageEmulatorSaveStates = new window.VintageSaveStateManager(window.VintageSaveStateConfig, {
                async captureState() {
                    const state = window.EJS_emulator?.gameManager?.getState?.();

                    return state instanceof Promise ? await state : state;
                },
                async restoreState(bytes) {
                    window.EJS_emulator?.gameManager?.loadState?.(bytes);
                },
            });
            window.VintageEmulatorSaveStates.init().finally(loadEmulatorJsScript);
        };

        const startEmulatorJsGamepad = () => {
            window.VintagePlayerGamepad.start({
                adapter: 'emulatorjs',
                target: '#game',
            });
        };

        if (window.VintagePlayerGamepad) {
            startEmulatorJsGamepad();
        } else {
            window.addEventListener('vintage-gamepad:ready', startEmulatorJsGamepad, { once: true });
        }

        if (window.VintageSaveStateManager) {
            startEmulatorJsSaveStates();
        } else {
            window.addEventListener('vintage-save-state:ready', startEmulatorJsSaveStates, { once: true });
        }
        
        // Callback when emulator is ready and game starts
        EJS_onGameStart = function() {
            hideEmulatorJSLoader();
        };
        
        // Fallback: Hide loader after emulator is ready (in case onGameStart doesn't fire)
        EJS_ready = function() {
            // Set a timeout to hide loader if game doesn't start within 10 seconds
            setTimeout(() => {
                hideEmulatorJSLoader();
            }, 10000);
        };

        // Fallback timeout to hide loader after 20 seconds
        setTimeout(() => {
            const loader = document.getElementById('emulatorjs-loader');
            if (loader && loader.style.display !== 'none') {
                hideEmulatorJSLoader();
            }
        }, 20000);
    </script>
    {{-- it stays always ON in SPA mode --}}
    {{-- Loaded dynamically after server-backed control settings are restored. --}}
    {{-- <script src="https://cdn.jsdelivr.net/gh/EmulatorJS/EmulatorJS@latest/data/loader.js"></script> --}}
    
    {{-- doesn't work on SPA, only full page reload --}}
    {{-- <script src="https://www.emulatorjs.com/loader.js"></script> --}}
    @endpush
</div>