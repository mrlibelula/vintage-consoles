@props(['active'])

@php
$classes = ($active ?? false)
            ? 'sepia_ inline-flex h-full whitespace-nowrap items-center px-1 text-xl leading-5 text-cod-gray-900 dark:text-rose-300 focus:outline-none smooth-300'
            : 'sepia_ inline-flex h-full whitespace-nowrap items-center px-1 text-xl leading-5 text-cod-gray-500 dark:text-cod-gray-400 hover:text-cod-gray-700 dark:hover:text-rose-300 focus:outline-none focus:text-cod-gray-700 dark:focus:text-cod-gray-300 smooth-300';
@endphp

<a wire:navigate {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
