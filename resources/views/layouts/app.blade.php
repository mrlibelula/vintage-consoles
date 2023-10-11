<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        {{-- <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" /> --}}

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">

        <!-- Iconset from semantic ui -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/components/icon.min.css" integrity="sha256-KyXPF3/VOPPst/NQOzCWr97QMfSfzJLyFT0o5lYJXiQ=" crossorigin="anonymous" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        <style>
            * {
                font-family: 'VT323', monospace;
                font-size: 1.32rem;
            }
        </style>
        
        @stack('styles')

        @livewireStyles
    </head>
    <body>
        <div class="isolate antialiased cursor-default text-2xl text-cod-gray-700 dark:text-cod-gray-200 bg-cod-gray-100 dark:bg-cod-gray-900">
            
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

                @livewire('navigation-menu')

                <x-top-spacer />
    
                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white dark:bg-cod-gray-950/40">
                        <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif
    
                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>
    
            @stack('modals')

            @stack('scripts')
    
            @livewireScripts
        </div>
    </body>
</html>
