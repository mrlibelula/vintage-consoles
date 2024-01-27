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
    </script>
    {{-- it stays always ON in SPA mode --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/gamepads@1.2.2/gamepads.min.js"></script> --}}
    {{-- <script src="https://raw.githack.com/ethanaobrien/Gamepad/main/gamepad.js"></script> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/nipplejs@0.10.1/dist/nipplejs.min.js"></script> --}}

    {{-- <script src="https://cdn.jsdelivr.net/gh/EmulatorJS/EmulatorJS@latest/data/loader.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/gh/EmulatorJS/EmulatorJS@4.0.7/data/loader.js"></script>
    {{-- <script src="https://cdn.jsdelivr.net/gh/EmulatorJS/EmulatorJS@4.0.7/data/loader.js"></script> --}}
    
    {{-- doesn't work on SPA, only full page reload --}}
    {{-- <script src="https://www.emulatorjs.com/loader.js"></script> --}}
    @endpush
</div>