<button x-cloak @click="cycleTheme()" {{ $attributes->merge(['class' => 'cursor-pointer w-5 h-5 text-cod-gray-700 dark:text-cod-gray-400 hover:text-cod-gray-800 dark:hover:text-cod-gray-100 smooth-300']) }}>
    <div x-show="theme === 'light'">
        <x-icons.sun />
    </div>
    <div x-show="theme === 'dark'">
        <x-icons.moon />
    </div>
    <div x-show="theme === 'system'">
        <x-icons.computer />
    </div>
</button>