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

        <x-app-font />

        <!-- Iconset -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/components/icon.min.css" integrity="sha256-KyXPF3/VOPPst/NQOzCWr97QMfSfzJLyFT0o5lYJXiQ=" crossorigin="anonymous" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">

        <!-- favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/games/nes.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @include('partials.pixel-cursors')

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
            <x-toaster />
    
            <div class="min-h-screen">

                <livewire:navigation />

                <x-top-spacer />
    
                <!-- Page Heading -->
                @if (isset($header))
                <header class="bg-cod-gray-100 dark:bg-cod-gray-950/40">
                    <div class="max-w-site mx-auto py-6 px-4 xl:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
                @endif
    
                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>

            <footer class="relative mt-12 overflow-hidden">
                {{-- pixel grid on exact #e11d48 tone; fades right; light/dark opacity --}}
                <div class="footer-grid pointer-events-none absolute inset-0" aria-hidden="true">
                    <img
                        src="{{ asset('images/bg-blue.png') }}"
                        alt=""
                        class="footer-grid-img select-none"
                    />
                </div>
                <div class="footer-grid-fade pointer-events-none absolute inset-0" aria-hidden="true"></div>

                {{-- rose rule separating main ↔ footer --}}
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 z-[1] h-px bg-gradient-to-r from-[#e11d48] via-[#e11d48]/50 to-transparent shadow-[0_0_8px_1px_rgba(225,29,72,0.4)]"
                    aria-hidden="true"
                ></div>

                <div class="relative z-[1] flex items-center justify-between gap-x-4 px-4 py-5 sm:px-6 lg:px-8 max-w-site mx-auto">
                    <a
                        wire:navigate
                        href="{{ route('about') }}"
                        @click="$dispatch('loader-top-on')"
                        class="group sepia_ relative inline-flex items-center gap-x-2 text-base tracking-wider text-white hover:text-white/80 dark:text-cod-gray-400 dark:hover:text-[#e11d48] smooth-300"
                    >
                        <x-pixelarticon
                            name="info-box"
                            :size="18"
                            class="opacity-95 group-hover:opacity-100 text-white group-hover:text-white/80 dark:text-cod-gray-400 dark:opacity-70 dark:group-hover:opacity-100 dark:group-hover:text-[#e11d48] smooth-300"
                        />
                        <span>{{ __('About') }}</span>
                    </a>

                    <nav class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-sm tracking-wider" aria-label="{{ __('Consoles') }}">
                        @foreach (app(\App\Services\GameRepository::class)->getConsoles() as $console)
                        <a
                            wire:navigate
                            href="/{{ $console->short_name }}"
                            @click="$dispatch('loader-top-on')"
                            class="text-white/70 hover:text-white dark:text-cod-gray-500 dark:hover:text-[#e11d48] smooth-300"
                        >
                            {{ strtoupper($console->short_name) }}
                        </a>
                        @endforeach
                    </nav>

                    <a href="https://libe.dev" target="_other_LIBEDEV_{{ uniqid() }}" class="group flex items-center gap-x-2">
                        <div class="h-4 w-4 opacity-30 group-hover:opacity-50 smooth-300 rounded-sm overflow-hidden">
                            <img class="w-full h-full" src="{{ asset('images/libesoft.io_inv.png') }}" alt="">
                        </div>
                        <div class="text-base text-[#1e2024] dark:text-[#6b7280] group-hover:text-cod-gray-800 dark:text-cod-gray-400_ dark:group-hover:text-cod-gray-300 smooth-300">
                            libe.dev
                        </div>
                    </a>
                </div>
            </footer>
    
            @stack('modals')

            @stack('scripts')

            {{-- Drop the old site-wide COI worker; it stamped COEP on the play page and killed YouTube. Emulator iframe re-registers a scoped worker. --}}
            <script>
                (function () {
                    if (!('serviceWorker' in navigator)) return;
                    navigator.serviceWorker.getRegistrations().then(function (regs) {
                        regs.forEach(function (reg) {
                            var url = (reg.active && reg.active.scriptURL)
                                || (reg.waiting && reg.waiting.scriptURL)
                                || (reg.installing && reg.installing.scriptURL)
                                || '';
                            if (url.indexOf('coi-serviceworker') !== -1 && url.indexOf('v=2') === -1) {
                                reg.unregister();
                            }
                        });
                    }).catch(function () {});
                })();
            </script>
    
            @livewireScripts
        </div>
    </body>
</html>
