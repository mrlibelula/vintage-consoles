<div>
    <!-- Loader Overlay -->
    <div id="emulator-loader" style="
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        color: white;
        font-family: system-ui, -apple-system, sans-serif;
    ">
        <!-- Spinner -->
        <div style="
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.2);
            border-top: 4px solid #e60012;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        "></div>
        <!-- Loading Text -->
        <div style="
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 8px;
        ">Loading {{ $title }}</div>
        <div style="
            font-size: 14px;
            opacity: 0.7;
        ">Please wait while the ROM loads...</div>
    </div>

    <div id="game"></div>
    @push('styles')
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Hide scrollbars during loading */
        #emulator-loader::-webkit-scrollbar {
            display: none;
        }
        
        #emulator-loader {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
    </style>
    @endpush
    @push('scripts')
    <script>
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
        
        // Callback when emulator is ready and game starts
        EJS_onGameStart = function() {
            console.log("EmulatorJS: Game has started, hiding loader");
            const loader = document.getElementById('emulator-loader');
            if (loader) {
                loader.style.opacity = '0';
                loader.style.transition = 'opacity 0.5s ease-out';
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 500);
            }
        };
        
        // Fallback: Hide loader after emulator is ready (in case onGameStart doesn't fire)
        EJS_ready = function() {
            console.log("EmulatorJS: Emulator ready");
            // Set a timeout to hide loader if game doesn't start within 10 seconds
            setTimeout(() => {
                const loader = document.getElementById('emulator-loader');
                if (loader && loader.style.display !== 'none') {
                    console.log("EmulatorJS: Fallback - hiding loader after timeout");
                    loader.style.opacity = '0';
                    loader.style.transition = 'opacity 0.5s ease-out';
                    setTimeout(() => {
                        loader.style.display = 'none';
                    }, 500);
                }
            }, 10000);
        };
    </script>
    {{-- it stays always ON in SPA mode --}}
    <script src="https://cdn.jsdelivr.net/gh/EmulatorJS/EmulatorJS@4.0.7/data/loader.js"></script>
    {{-- <script src="https://cdn.jsdelivr.net/gh/EmulatorJS/EmulatorJS@latest/data/loader.js"></script> --}}
    
    {{-- doesn't work on SPA, only full page reload --}}
    {{-- <script src="https://www.emulatorjs.com/loader.js"></script> --}}
    @endpush
</div>