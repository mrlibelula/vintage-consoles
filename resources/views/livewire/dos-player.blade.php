<div>
    @push('styles')
    <link rel="stylesheet" href="https://v8.js-dos.com/latest/js-dos.css">
    <style>
        #dosbox { height: 100vh; }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Hide scrollbars during loading */
        #dos-loader::-webkit-scrollbar {
            display: none;
        }
        
        #dos-loader {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
    </style>
    @endpush

    <!-- DOS Loader Overlay -->
    <div id="dos-loader" style="
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
            border-top: 4px solid #0078d4;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        "></div>
        <!-- Loading Text -->
        <div style="
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 8px;
        ">Loading {{ $game['title'] ?? 'DOS Game' }}</div>
        <div style="
            font-size: 14px;
            opacity: 0.7;
        ">Initializing JS-DOS emulator...</div>
    </div>

    <div id="dosbox"></div>

    <script src="https://v8.js-dos.com/latest/js-dos.js"></script>
    <script>
        
        const bundleUrl = "{{ $game['rom'] }}";

        function hideLoader() {
            console.log("JS-DOS: Hiding loader");
            const loader = document.getElementById('dos-loader');
            if (loader) {
                loader.style.opacity = '0';
                loader.style.transition = 'opacity 0.5s ease-out';
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 500);
            }
        }

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
                
                // Hide loader when emulator starts
                hideLoader();
                
            } catch (error) {
                console.error("JS-DOS: Error starting emulator:", error);
                // Hide loader even on error to prevent infinite loading
                setTimeout(hideLoader, 2000);
            }
        }

        // Fallback timeout to hide loader in case something goes wrong
        setTimeout(() => {
            const loader = document.getElementById('dos-loader');
            if (loader && loader.style.display !== 'none') {
                console.log("JS-DOS: Fallback - hiding loader after timeout");
                hideLoader();
            }
        }, 15000); // 15 seconds timeout for DOS games (they can take longer)

        // Start the emulator as soon as the page loads
        window.onload = startEmulator;
    </script>
</div>
