<div>
    <x-slot name="header">
        <div class="text-2xl lg:text-3xl ">
            About
        </div>
    </x-slot>
    <x-container class=" text-cod-gray-600 dark:text-cod-gray-500 mt-6">
        <div class="flex flex-col gap-y-[5rem] lg:flex-row lg:gap-x-[5rem]">
            <div class="flex flex-col gap-y-4 leading-tight">
                <p>Introducing <span class=" text-cod-gray-900 dark:text-cod-gray-300">"Vintage Game Consoles"</span>, my portfolio-oriented vintage console game emulator web app made with <a href="https://tallstack.dev/" class="link" target="_other_TALL">TALL stack</a>!.</p>
                <p>I'm a passionate web developer and gamer, and I'm thrilled to share my project with you. <span class=" text-cod-gray-900 dark:text-cod-gray-300">"Vintage Game Consoles"</span> is a full-stack web app that allows you to play lots of vintage console games right in your browser.</p>
                <p>I built <span class=" text-cod-gray-900 dark:text-cod-gray-300">"Vintage Game Consoles"</span> to showcase my skills and knowledge in web development, and to create a fun and engaging experience for retro gaming fans. With <span class=" text-cod-gray-900 dark:text-cod-gray-300">"Vintage Game Consoles"</span>, you can easily browse and launch games from a variety of consoles, including the <a wire:navigate.hover @click="$dispatch('loader-top-on')" href="{{ route('home', 'nes') }}" class="link">NES</a>, <a wire:navigate.hover @click="$dispatch('loader-top-on')" href="{{ route('home', 'snes') }}" class="link">SNES</a>, <a wire:navigate.hover @click="$dispatch('loader-top-on')" href="{{ route('home', 'arcade') }}" class="link">Arcade</a>, and more. I've also implemented a variety of features to enhance your gaming experience, such as save states, cheats, multiplayer and live chat support.</p>
                <p>I'm still under development, but I'm excited to continue working on <span class=" text-cod-gray-900 dark:text-cod-gray-300">"Vintage Game Consoles"</span> and adding new features and improvements. I encourage you to connect your pad, check it out, and let me know what you think!.</p>
                <p>Check out other projects at my <a href="https://libe.dev/projects/" class="link" target="_other_LIBE">portfolio</a>.</p>
            </div>

            <div class="flex justify-center lg:justify-end">
                <x-libe-profile-card />
            </div>

        </div>
    </x-container>
</div>
