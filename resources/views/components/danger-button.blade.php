<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-rose-600 border border-transparent rounded-md text-xl text-white uppercase tracking-widest hover:bg-rose-500 active:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 dark:focus:ring-offset-cod-gray-800 transition ease-in-out duration-150 leading-none']) }}>
    {{ $slot }}
</button>
