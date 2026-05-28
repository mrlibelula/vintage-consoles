<link rel="preload" href="{{ $fontUrl }}" as="font" type="{{ $mimeType }}" crossorigin>

<style>
    @font-face {
        font-family: '{{ $font->family_name }}';
        src: url('{{ $fontUrl }}') format('{{ $cssFormat }}');
        font-weight: 400;
        font-style: normal;
        font-display: block;
    }

    html {
        --app-font-family: {!! $cssFamily !!};
    }

    * {
        font-family: var(--app-font-family);
        font-size: 1.32rem;
    }

    html:not(.fonts-loaded) body {
        visibility: hidden;
    }

    html.fonts-loaded body {
        visibility: visible;
    }
</style>

<noscript>
    <style>html body { visibility: visible !important; }</style>
</noscript>

<script>
    (() => {
        const familyName = @json($font->family_name);
        const fontSpec = `400 1.32rem "${familyName}"`;
        const maxWaitMs = 15000;
        let revealed = false;

        const showContent = () => {
            if (revealed) {
                return;
            }

            revealed = true;
            document.documentElement.classList.add('fonts-loaded');
        };

        const waitForFont = async () => {
            if (!document.fonts?.load) {
                showContent();

                return;
            }

            try {
                await document.fonts.load(fontSpec);
            } catch {
                // Fall through to polling / timeout.
            }

            if (document.fonts.check(fontSpec)) {
                showContent();

                return;
            }

            const deadline = Date.now() + maxWaitMs;

            const poll = () => {
                if (document.fonts.check(fontSpec)) {
                    showContent();

                    return;
                }

                if (Date.now() < deadline) {
                    setTimeout(poll, 50);

                    return;
                }

                showContent();
            };

            poll();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => waitForFont());
        } else {
            waitForFont();
        }
    })();
</script>
