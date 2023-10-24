<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-cod-gray-100/60 dark:bg-cod-gray-900 border border-cod-gray-300 dark:border-cod-gray-500 rounded-md text-xl text-cod-gray-700 dark:text-cod-gray-300 uppercase tracking-widest shadow-sm hover:bg-cod-gray-50 dark:hover:bg-cod-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 dark:focus:ring-offset-cod-gray-800 disabled:opacity-25 transition ease-in-out duration-150 leading-none']) }}>
    {{ $slot }}
</button>
