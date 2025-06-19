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
            try {
                const dosbox = document.getElementById("dosbox");
                console.log("JS-DOS: Starting emulator initialization");
                
                const ci = await Dos(dosbox, {
                    wdosboxUrl: "https://v8.js-dos.com/latest/wdosbox.js",
                    url: bundleUrl,
                    autolock: true,
                });
                
                console.log("JS-DOS: Emulator initialized");
                ci.setTheme("dark");
                ci.setAutoStart(true);
                
                // Hide the universal loader when emulator starts
                if (window.hideUniversalLoader) {
                    window.hideUniversalLoader();
                }
                
            } catch (error) {
                console.error("JS-DOS: Error starting emulator:", error);
                // Hide loader even on error to prevent infinite loading
                setTimeout(() => {
                    if (window.hideUniversalLoader) {
                        window.hideUniversalLoader();
                    }
                }, 2000);
            }
        }

        // Start the emulator as soon as the page loads
        window.onload = startEmulator;
    </script>
</div>
