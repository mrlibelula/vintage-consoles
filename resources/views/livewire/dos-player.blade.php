<div>
    @push('styles')
    <link rel="stylesheet" href="https://v8.js-dos.com/latest/js-dos.css">
    <style>
        #dosbox { height: 100vh; }
    </style>
    @endpush
    <div id="dosbox"></div>

    <script src="https://v8.js-dos.com/latest/js-dos.js"></script>
    <script>
        
        const bundleUrl = "{{ $game['rom'] }}";

        async function startEmulator() {
            const dosbox = document.getElementById("dosbox");
            const ci = await Dos(dosbox, {
                wdosboxUrl: "https://v8.js-dos.com/latest/wdosbox.js",
                url: bundleUrl,
                autolock: true,
            });
            ci.setTheme("dark");
            ci.setAutoStart(true);
        }

        // Start the emulator as soon as the page loads
        window.onload = startEmulator;
    </script>
</div>
