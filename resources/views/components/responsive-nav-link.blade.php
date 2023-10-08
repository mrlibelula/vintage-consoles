@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full pl-3 pr-4 py-2 border-l-4 border-rose-400 dark:border-rose-600 text-left text-lg text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-900/50 focus:outline-none focus:text-rose-800 dark:focus:text-rose-200 focus:bg-rose-100 dark:focus:bg-rose-900 focus:border-rose-700 dark:focus:border-rose-300 transition duration-300 ease-in-out'
            : 'block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-lg text-cod-gray-600 dark:text-cod-gray-400 hover:text-cod-gray-800 dark:hover:text-cod-gray-200 hover:bg-cod-gray-50 dark:hover:bg-rose-900/50 hover:border-cod-gray-300 dark:hover:border-rose-600 focus:outline-none focus:text-cod-gray-800 dark:focus:text-cod-gray-200 focus:bg-cod-gray-50 dark:focus:bg-cod-gray-700 focus:border-cod-gray-300 dark:focus:border-cod-gray-600 transition duration-300 ease-in-out';
@endphp

<a wire:navigate {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
