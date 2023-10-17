@props(['active'])

@php
$classes = ($active ?? false)
            ? 'sepia_ inline-flex whitespace-nowrap items-center px-1 pt-1 border-b-[4.7px] border-rose-400 dark:border-rose-600 text-xl leading-5 text-cod-gray-900 dark:text-rose-300 focus:outline-none focus:border-rose-700 smooth-300'
            : 'sepia_ inline-flex whitespace-nowrap items-center px-1 pt-1 border-b-[4.7px] border-transparent text-xl leading-5 text-cod-gray-500 dark:text-cod-gray-400 hover:text-cod-gray-700 dark:hover:text-rose-300 hover:border-cod-gray-300 dark:hover:border-cod-gray-700 focus:outline-none focus:text-cod-gray-700 dark:focus:text-cod-gray-300 focus:border-cod-gray-300 dark:focus:border-cod-gray-700 smooth-300';
@endphp

<a wire:navigate.hover {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
