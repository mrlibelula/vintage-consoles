<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-cloak
    x-data="{ 
        theme: localStorage.getItem('theme') || 'system',
        isDark: false,
        init() {
            // Migrate from old 'dark' key to new 'theme' key
            if (!localStorage.getItem('theme') && localStorage.getItem('dark')) {
                const oldDark = localStorage.getItem('dark') === 'true';
                this.theme = oldDark ? 'dark' : 'light';
                localStorage.removeItem('dark');
            }
            
            this.updateTheme();
            this.$watch('theme', val => {
                localStorage.setItem('theme', val);
                this.updateTheme();
            });
            
            // Listen for system theme changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.theme === 'system') {
                    this.updateTheme();
                }
            });
        },
        updateTheme() {
            if (this.theme === 'system') {
                this.isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            } else {
                this.isDark = this.theme === 'dark';
            }
        },
        cycleTheme() {
            const themes = ['light', 'dark', 'system'];
            const currentIndex = themes.indexOf(this.theme);
            this.theme = themes[(currentIndex + 1) % themes.length];
        }
    }"
    x-bind:class="{ 'dark': isDark }"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        @stack('meta')

        <title>{{ config('app.name', 'Vintage Consoles') }}</title>

        <!-- Fonts -->
        {{-- <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" /> --}}

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=VT323&display=block" rel="stylesheet">
        <link rel="preload" href="https://fonts.googleapis.com/css2?family=VT323&display=block" as="style">

        <!-- Iconset -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/components/icon.min.css" integrity="sha256-KyXPF3/VOPPst/NQOzCWr97QMfSfzJLyFT0o5lYJXiQ=" crossorigin="anonymous" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">

        <!-- favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/games/nes.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        <style>
            /* Prevent FOUT without jarring flash */
            * {
                font-family: 'VT323', monospace;
                font-size: 1.32rem;
            }
            
            /* Hide text until font loads */
            body {
                color: transparent;
                transition: color 0.2s ease-in-out;
            }
            
            /* Show text once font is loaded */
            html.fonts-loaded body {
                color: inherit;
            }
            
            /* Keep background and layout visible */
            body {
                background-color: inherit;
            }
        </style>
        
        <!-- Font loading script -->
        <script>
            // Optimized font loading detection
            function showContent() {
                document.documentElement.classList.add('fonts-loaded');
            }
            
            // Check if VT323 font is loaded
            if (document.fonts && document.fonts.check) {
                // Modern browsers - check specifically for VT323
                const checkVT323 = () => {
                    if (document.fonts.check('16px VT323')) {
                        showContent();
                    } else {
                        setTimeout(checkVT323, 10);
                    }
                };
                
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', checkVT323);
                } else {
                    checkVT323();
                }
            } else {
                // Fallback for older browsers
                document.fonts.ready.then(showContent);
            }
            
            // Reduced safety timeout - show after 1 second max
            setTimeout(showContent, 1000);
        </script>
        
        @stack('styles')

        @livewireStyles
    </head>
    <body>  
        <div class="isolate antialiased cursor-default text-2xl text-cod-gray-700 dark:text-cod-gray-600 bg-cod-gray-200 dark:bg-cod-gray-950/95 ">
            
            <!-- bg shadow -->
            {{-- <div class="absolute h-full inset-x-0 -z-10 transform-gpu overflow-hidden blur-3xl">
                <svg class="relative animate-pulse left-[calc(50%-11rem)] -z-10 h-[21.1875rem] max-w-none -translate-x-1/2 rotate-[30deg] sm:left-[calc(50%-30rem)] sm:h-[42.375rem]" viewBox="0 0 1155 678" xmlns="http://www.w3.org/2000/svg">
                    <path fill="url(#45de2b6b-92d5-4d68-a6a0-9b9b2abad533)" fill-opacity=".3" d="M317.219 518.975L203.852 678 0 438.341l317.219 80.634 204.172-286.402c1.307 132.337 45.083 346.658 209.733 145.248C936.936 126.058 882.053-94.234 1031.02 41.331c119.18 108.451 130.68 295.337 121.53 375.223L855 299l21.173 362.054-558.954-142.079z" />
                    <defs>
                        <linearGradient id="45de2b6b-92d5-4d68-a6a0-9b9b2abad533" x1="1155.49" x2="-78.208" y1=".177" y2="474.645" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#181818"></stop>
                            <stop offset="1" stop-color="#ffffff"></stop>
                        </linearGradient>
                    </defs>
                </svg>
            </div> --}}

            <x-banner />
    
            <div class="min-h-screen">

                <livewire:navigation />

                <x-top-spacer />
    
                <!-- Page Heading -->
                @if (isset($header))
                <header class="bg-cod-gray-100 dark:bg-cod-gray-950/40">
                    <div class="max-w-5xl mx-auto py-6 px-4 xl:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
                @endif
    
                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>

            @if (request()->routeIs('play'))
            <div></div>
            @else
            <footer>
                <a href="https://libe.dev" target="_other_LIBEDEV_{{ uniqid() }}" class="group flex items-center justify-center gap-y-2 gap-x-2 pt-10 pb-6">
                    <div class="h-4 w-4 opacity-30 group-hover:opacity-50 smooth-300 rounded-sm overflow-hidden">
                        <img class="w-full h-full" src="{{ asset('images/libesoft.io_inv.png') }}" alt="">
                    </div>
                    <div class="text-base text-cod-gray-600/80 group-hover:text-cod-gray-500 smooth-300">
                        libe.dev
                    </div>
                </a>
            </footer>
            @endif
    
            @stack('modals')

            @stack('scripts')
    
            @livewireScripts
        </div>
    </body>
</html>
