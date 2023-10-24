<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-cod-gray-900 dark:bg-cod-gray-200 border border-transparent rounded-md font-semibold text-xl text-white dark:text-cod-gray-800 uppercase tracking-widest hover:bg-cod-gray-700 dark:hover:bg-cod-gray-100/60 focus:bg-cod-gray-700 dark:focus:bg-cod-gray-100/60 active:bg-cod-gray-900 dark:active:bg-cod-gray-300 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 dark:focus:ring-offset-cod-gray-800 transition ease-in-out duration-150 leading-none']) }}>
    {{ $slot }}
</button>
