<button {{ $attributes->merge(['class' => 'text-sky-700 dark:text-sky-400 tracking-wide lowercase text-sm font-mono font-semibold leading-none hover:text-rose-700 dark:hover:text-rose-300 smooth-300 cursor-pointer']) }}>
    {{ $slot }}
</button>