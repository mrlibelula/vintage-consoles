<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <x-app-font />

        <!-- Iconset -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/components/icon.min.css" integrity="sha256-KyXPF3/VOPPst/NQOzCWr97QMfSfzJLyFT0o5lYJXiQ=" crossorigin="anonymous" />

        <script>
            (() => {
                const theme = localStorage.getItem('theme') || 'system'
                const isDark =
                    theme === 'dark' ||
                    (theme === 'system' &&
                        window.matchMedia &&
                        window.matchMedia('(prefers-color-scheme: dark)').matches)

                document.documentElement.classList.toggle('dark', Boolean(isDark))
            })()
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="cursor-default sepia_">
        <div class="antialiased text-cod-gray-900 dark:text-cod-gray-400 bg-cod-gray-100 dark:bg-cod-gray-800">
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
