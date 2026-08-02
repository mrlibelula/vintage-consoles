<button x-cloak @click="cycleTheme()" {{ $attributes->merge(['class' => 'cursor-pointer inline-flex items-center justify-center leading-none shrink-0 text-cod-gray-700 dark:text-cod-gray-400 hover:text-cod-gray-800 dark:hover:text-cod-gray-100 smooth-300']) }}>
    <div x-show="theme === 'light'" class="flex items-center leading-none">
        <x-pixelarticon name="sun" :size="26" />
    </div>
    <div x-show="theme === 'dark'" class="flex items-center leading-none">
        <x-pixelarticon name="moon" :size="26" />
    </div>
    <div x-show="theme === 'system'" class="flex items-center leading-none">
        <x-pixelarticon name="computer" :size="26" />
    </div>
</button>
