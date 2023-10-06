<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Libe.dev - Vintage Consoles</title>
        
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
        
        <script src="https://cdn.tailwindcss.com"></script>

        <style>
            * {
                font-family: 'VT323', monospace;
                font-size: 1.32rem;
            }

            a:hover {
                color: #60a5fa;
            }
        </style>

        @livewireStyles

    </head>
    <body class="isolate bg-blue-950 antialiased overflow-hidden overflow-y-auto cursor-default transition duration-500 ease-in-out">
        <div class="flex flex-col gap-y-8 h-screen">
            <div class="absolute h-full inset-x-0 -z-10 transform-gpu overflow-hidden blur-3xl">
                <svg class="relative left-[calc(50%-11rem)] -z-10 h-[21.1875rem] max-w-none -translate-x-1/2 rotate-[30deg] sm:left-[calc(50%-30rem)] sm:h-[42.375rem]" viewBox="0 0 1155 678" xmlns="http://www.w3.org/2000/svg">
                    <path fill="url(#45de2b6b-92d5-4d68-a6a0-9b9b2abad533)" fill-opacity=".3" d="M317.219 518.975L203.852 678 0 438.341l317.219 80.634 204.172-286.402c1.307 132.337 45.083 346.658 209.733 145.248C936.936 126.058 882.053-94.234 1031.02 41.331c119.18 108.451 130.68 295.337 121.53 375.223L855 299l21.173 362.054-558.954-142.079z" />
                    <defs>
                        <linearGradient id="45de2b6b-92d5-4d68-a6a0-9b9b2abad533" x1="1155.49" x2="-78.208" y1=".177" y2="474.645" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#c026d3"></stop>
                            <stop offset="1" stop-color="#ffffff"></stop>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <div class="flex flex-col py-6 px-8 h-full">
                <nav class="flex items-center justify-between" aria-label="Global">
                    <div class="flex lg:flex-1">
                        <a wire:navigate href="#" class="-m-1.5 p-1.5">
                            <span class="sr-only">Libe.dev logo</span>
                            <div class="flex items-center group">
                                <x-libe-dev-logo class="w-10 rounded-none rounded-l" />
                                <img class=" ml-[-0.128rem] w-[5.5rem] rounded-r" src="{{ asset('images/games/nes_controller.png') }}" alt="">
                            </div>
                        </a>
                    </div>
                    <div class="flex lg:hidden">
                        <button type="button" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-400">
                            <span class="sr-only">Open main menu</span>
                            <!-- Heroicon name: outline/bars-3 -->
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                    </div>
                    <div class="hidden lg:flex lg:gap-x-12">
                        <a wire:navigate href="#" class="  leading-6 text-gray-200">Home</a>
                        <a wire:navigate href="#" class="  leading-6 text-gray-200">NES</a>
                        <a wire:navigate href="#" class="  leading-6 text-gray-200">SNES</a>
                        <a wire:navigate href="#" class="  leading-6 text-gray-200">Arcade</a>
                        <a wire:navigate href="#" class="  leading-6 text-gray-200">PC</a>
                    </div>
                    <div class="hidden lg:flex lg:flex-1 lg:justify-end items-center gap-x-8">
                        @if (Route::has('login'))
                        <a wire:navigate href="{{ route('register') }}" class="leading-6 text-gray-200">
                            Register
                        </a>
                        <a wire:navigate href="{{ route('login') }}" class="leading-6 text-gray-200">
                            Log in <span aria-hidden="true">&rarr;</span>
                        </a>
                        @endif
                    </div>
                </nav>
                <!-- Mobile menu, show/hide based on menu open state. -->
                <div role="dialog" aria-modal="true">
                    <div focus="true" class="hidden fixed inset-0 z-10 overflow-y-auto bg-white px-6 py-6 lg:hidden">
                        <div class="flex items-center justify-between">
                            <a wire:navigate href="#" class="-m-1.5 p-1.5">
                            <span class="sr-only">Your Company</span>
                            <img class="h-8" src="https://tailwindui.com/img/logos/mark.svg?color=blue&shade=600" alt="">
                            </a>
                            <button type="button" class="-m-2.5 rounded-md p-2.5 text-gray-700">
                                <span class="sr-only">Close menu</span>
                                <!-- Heroicon name: outline/x-mark -->
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-6 flow-root">
                            <div class="-my-6 divide-y divide-gray-500/10">
                                <div class="space-y-2 py-6">
                                    <a wire:navigate href="#" class="-mx-3 block rounded-lg py-2 px-3   leading-7 text-gray-200 hover:bg-gray-400/10">Home</a>
                                    <a wire:navigate href="#" class="-mx-3 block rounded-lg py-2 px-3   leading-7 text-gray-200 hover:bg-gray-400/10">NES</a>
                                    <a wire:navigate href="#" class="-mx-3 block rounded-lg py-2 px-3   leading-7 text-gray-200 hover:bg-gray-400/10">SNES</a>
                                    <a wire:navigate href="#" class="-mx-3 block rounded-lg py-2 px-3   leading-7 text-gray-200 hover:bg-gray-400/10">Arcade</a>
                                    <a wire:navigate href="#" class="-mx-3 block rounded-lg py-2 px-3   leading-7 text-gray-200 hover:bg-gray-400/10">PC</a>
                                </div>
                                <div class="py-6">
                                    <a wire:navigate href="{{ route('login') }}" class="-mx-3 block rounded-lg py-2.5 px-3   leading-6 text-gray-200 hover:bg-gray-400/10">
                                        Log in
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <main class="flex flex-col justify-center h-full _-mt-12">
                    <div class="relative">
                        <div class="mx-auto max-w-2xl">
                            <div class="hidden sm:mb-8 sm:flex sm:justify-center">
                                <div class="relative rounded-full py-1 px-3  leading-6 text-gray-400 ring-1 ring-blue-800  hover:ring-gray-900/20">
                                    Announcing our next round of funding. <a wire:navigate href="#" class=" text-blue-400"><span class="absolute inset-0" aria-hidden="true"></span>
                                        Read more <span aria-hidden="true">&rarr;</span></a>
                                </div>
                            </div>
                            <div class="text-center">
                                <h1 class="text-4xl font-bold text-gray-300 sm:text-6xl text-transparent bg-clip-text bg-gradient-to-b from-gray-600 via-white to-gray-600 animate-pulse">
                                    Vintage Consoles
                                </h1>
                                <p class="mt-6 leading-8 text-gray-300">
                                    {{ \App\Service\Tool::randomItem([
                                        "Step into nostalgia's embrace with our Online Vintage Console Emulator Game Station. Relive cherished memories and play timeless classics. Start your retro gaming journey today!",
                                        "Dive into the past, relive the classics! Discover the ultimate gaming experience with our Online Vintage Console Emulator Game Station. Begin your retro adventure now.",
                                        "Unleash nostalgia, power up the classics! Immerse yourself in gaming history with our Online Vintage Console Emulator Game Station. Start your retro gaming odyssey today!",
                                        "Rewind time and play the classics! Our Online Vintage Console Emulator Game Station brings retro gaming to your fingertips. Begin your journey through gaming history now!",
                                        "Revive the past, play the legends! Explore gaming's golden era with our Online Vintage Console Emulator Game Station. Start your retro gaming adventure today and create timeless memories.",
                                    ]) }}
                                </p>
                                <div class="mt-10 flex items-center justify-center gap-x-6">
                                    <a wire:navigate href="#" class="rounded-md bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-600 hover:to-sky-500 transition duration-500 ease-in-out px-3.5 py-1.5 leading-7 text-white shadow shadow-black focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 cursor-pointer">
                                        Get started
                                    </a>
                                    <a wire:navigate href="#" class="  leading-7 text-gray-200">Learn more <span aria-hidden="true">→</span></a>
                                </div>
                            </div>
                        </div>
                        <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]">
                            <svg class="relative left-[calc(50%+3rem)] h-[21.1875rem] max-w-none -translate-x-1/2 sm:left-[calc(50%+36rem)] sm:h-[42.375rem]" viewBox="0 0 1155 678" xmlns="http://www.w3.org/2000/svg">
                                <path fill="url(#ecb5b0c9-546c-4772-8c71-4d3f06d544bc)" fill-opacity=".3" d="M317.219 518.975L203.852 678 0 438.341l317.219 80.634 204.172-286.402c1.307 132.337 45.083 346.658 209.733 145.248C936.936 126.058 882.053-94.234 1031.02 41.331c119.18 108.451 130.68 295.337 121.53 375.223L855 299l21.173 362.054-558.954-142.079z" />
                                <defs>
                                    <linearGradient id="ecb5b0c9-546c-4772-8c71-4d3f06d544bc" x1="1155.49" x2="-78.208" y1=".177" y2="474.645" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#ffffff"></stop>
                                        <stop offset="1" stop-color="#2563eb"></stop>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        @livewireScripts

    </body>
</html>
