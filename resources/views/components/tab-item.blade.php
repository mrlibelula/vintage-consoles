@props(['active' => false])

@php
    $classes = $active
        ? 'inline-flex items-center justify-center text-base md:text-xl rounded-t-md text-cod-gray-50 dark:text-cod-gray-50 bg-cod-gray-500 dark:bg-cod-gray-800 w-full px-1 xl:px-3 whitespace-nowrap cursor-pointer dark:hover:bg-cod-gray-800 dark:hover:text-rose-300 smooth-300'
        : 'inline-flex items-center justify-center text-base md:text-xl rounded-t-md text-cod-gray-500 dark:text-cod-gray-400 bg-cod-gray-300/50 dark:bg-cod-gray-800/40 w-full px-1 xl:px-3 whitespace-nowrap cursor-pointer dark:hover:bg-cod-gray-800 dark:hover:text-rose-300 smooth-300';
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>