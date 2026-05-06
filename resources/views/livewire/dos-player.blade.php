<div>
    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/js-dos@8.3.20/dist/js-dos.css">
    <style>
        #dosbox {
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background: #000000;
        }
    </style>
    @endpush

    <div id="dosbox"></div>

    <script>
        if (window.self !== window.top && navigator.keyboard?.lock) {
            try {
                navigator.keyboard.lock = () => Promise.resolve();
                navigator.keyboard.unlock = () => {};
            } catch (error) {
                console.warn('Keyboard Lock API cannot be disabled for the iframe.', error);
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/js-dos@8.3.20/dist/js-dos.js"></script>
    <script>

        const bundleUrl = "{{ $game['rom'] }}";
        const jsDosPathPrefix = 'https://cdn.jsdelivr.net/npm/js-dos@8.3.20/dist/emulators/';
        let dosProps = null;

        const startJsDosGamepad = () => {
            window.VintagePlayerGamepad.start({
                adapter: 'jsdos',
                target: '#dosbox',
            });
        };

        if (window.VintagePlayerGamepad) {
            startJsDosGamepad();
        } else {
            window.addEventListener('vintage-gamepad:ready', startJsDosGamepad, { once: true });
        }

        async function startEmulator() {
            try {
                if (dosProps?.stop) {
                    await dosProps.stop();
                }

                const dosbox = document.getElementById("dosbox");
                dosbox.innerHTML = '';

                dosProps = await Dos(dosbox, {
                    pathPrefix: jsDosPathPrefix,
                    url: bundleUrl,
                    autolock: false,
                });

                dosProps.setTheme("dark");
                dosProps.setAutoStart(true);

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
        window.addEventListener('load', () => startEmulator());
    </script>
</div>
