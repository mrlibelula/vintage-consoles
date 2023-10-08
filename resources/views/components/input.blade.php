@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-cod-gray-300 dark:border-cod-gray-700 dark:bg-cod-gray-900 dark:text-cod-gray-200 focus:border-rose-500 dark:focus:border-rose-600 focus:ring-rose-500 dark:focus:ring-rose-600 rounded-md shadow-sm text-2xl dark:placeholder-cod-gray-700 sepia_']) !!}>
