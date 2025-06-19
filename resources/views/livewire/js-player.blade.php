<div>
    <div id="game"></div>
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
            console.log("EmulatorJS: Game has started, hiding universal loader");
            if (window.hideUniversalLoader) {
                window.hideUniversalLoader();
            }
        };
        
        // Fallback: Hide loader after emulator is ready (in case onGameStart doesn't fire)
        EJS_ready = function() {
            console.log("EmulatorJS: Emulator ready");
            // Set a timeout to hide loader if game doesn't start within 10 seconds
            setTimeout(() => {
                if (window.hideUniversalLoader) {
                    console.log("EmulatorJS: Fallback - hiding universal loader after timeout");
                    window.hideUniversalLoader();
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