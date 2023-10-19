@props(['active' => false])

@php
    $classes = $active
        ? 'text-base md:text-xl rounded-t-md bg-cod-gray-800 w-full text-center px-1 xl:px-3 whitespace-nowrap cursor-pointer hover:bg-cod-gray-800 hover:text-rose-300 smooth-300 text-cod-gray-50'
        : 'text-base md:text-xl rounded-t-md bg-cod-gray-800/40 w-full text-center px-1 xl:px-3 whitespace-nowrap cursor-pointer hover:bg-cod-gray-800 hover:text-rose-300 smooth-300 text-cod-gray-400';
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>