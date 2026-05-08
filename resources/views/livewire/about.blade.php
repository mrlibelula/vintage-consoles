<div>
    <x-slot name="header">
        <div class="text-2xl lg:text-3xl ">
            About
        </div>
    </x-slot>
    <x-container class=" text-cod-gray-600 dark:text-cod-gray-500 mt-6">
        <div class="flex flex-col gap-y-[5rem] lg:flex-row lg:gap-x-[5rem]">
            <div class="flex flex-col gap-y-4 leading-tight">
                <p><span class=" text-cod-gray-900 dark:text-cod-gray-300">"Vintage Game Consoles"</span> is a portfolio focused retro console game emulator web app I built to bring classic games into the browser.</p>
                <p>I'm a web developer who just loves playing a few games, and this project is where I combine both: it’s a full stack app that lets you discover and play a growing library of vintage titles without leaving the page.</p>
                <p>I built <span class=" text-cod-gray-900 dark:text-cod-gray-300">"Vintage Game Consoles"</span> to showcase practical product work, not just demos. You can browse and launch games across multiple consoles, including the <a wire:navigate.hover @click="$dispatch('loader-top-on')" href="{{ route('home', 'nes') }}" class="link">NES</a>, <a wire:navigate.hover @click="$dispatch('loader-top-on')" href="{{ route('home', 'snes') }}" class="link">SNES</a>, <a wire:navigate.hover @click="$dispatch('loader-top-on')" href="{{ route('home', 'arcade') }}" class="link">Arcade</a>, and more. I’ve also added features to improve the experience, like save states, cheats, multiplayer, and live chat rooms on each game.</p>
                <p>This project is still in active development, and I’m continuing to iterate on it with new consoles, games, and quality of life improvements. Connect a controller, try it out, and let me know what you think.</p>
                <p>You can find more projects on my <a href="https://libe.dev/" class="link" target="_other_LIBE">portfolio</a>.</p>
            </div>

            <div class="flex justify-center lg:justify-end">
                <x-libe-profile-card />
            </div>

        </div>
    </x-container>
</div>
