@props(['active' => false])

@php
    $classes = $active
        ? 'inline-flex items-center justify-center text-base md:text-xl rounded-t-md border border-b-0 border-cod-gray-400 dark:border-cod-gray-700 text-cod-gray-900 dark:text-cod-gray-50 bg-[#d0d2d8] dark:bg-cod-gray-800 w-full px-1 xl:px-3 whitespace-nowrap cursor-pointer hover:text-rose-700 dark:hover:bg-cod-gray-800 dark:hover:text-rose-300 smooth-300'
        : 'inline-flex items-center justify-center text-base md:text-xl rounded-t-md border border-b-0 border-cod-gray-400 dark:border-cod-gray-700 text-cod-gray-600 dark:text-cod-gray-400 bg-cod-gray-300/70 dark:bg-cod-gray-800/40 w-full px-1 xl:px-3 whitespace-nowrap cursor-pointer hover:bg-cod-gray-300 dark:hover:bg-cod-gray-800 dark:hover:text-rose-300 smooth-300';
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
