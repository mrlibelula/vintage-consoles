@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-2xl text-cod-gray-700 dark:text-cod-gray-300']) }}>
    {{ $value ?? $slot }}
</label>
