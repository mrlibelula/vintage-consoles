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

    body {
        color: transparent;
        transition: color 0.2s ease-in-out;
        background-color: inherit;
    }

    html.fonts-loaded body {
        color: inherit;
    }
</style>

<script>
    (() => {
        const familyName = @json($font->family_name);
        const fontSpec = `16px ${familyName}`;

        const showContent = () => {
            document.documentElement.classList.add('fonts-loaded');
        };

        const waitForFont = () => {
            if (!document.fonts || !document.fonts.load) {
                showContent();
                return;
            }

            document.fonts.load(fontSpec).then(showContent).catch(showContent);

            const poll = () => {
                if (document.fonts.check(fontSpec)) {
                    showContent();
                    return;
                }

                setTimeout(poll, 10);
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', poll);
            } else {
                poll();
            }
        };

        waitForFont();
        setTimeout(showContent, 1000);
    })();
</script>
