<button x-cloak @click="darkMode = !darkMode" {{ $attributes->merge(['class' => 'cursor-pointer w-5 h-5 text-cod-gray-700 dark:text-cod-gray-400 hover:text-cod-gray-800 dark:hover:text-cod-gray-100 smooth-300']) }}>
    <div x-show="!darkMode">
        <x-icons.moon />
    </div>
    <div x-show="darkMode">
        <x-icons.sun />
    </div>
</button>