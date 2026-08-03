@props(['contain' => true])

<div @class([
    'leading-tight rounded-b-md border border-t-0 border-cod-gray-400 bg-[#d0d2d8] dark:border-cod-gray-700 dark:bg-cod-gray-800 w-full flex flex-col',
    'min-h-0 flex-1 overflow-hidden' => $contain,
    'overflow-visible' => ! $contain,
])>
    <div {{ $attributes->class([
        'flex flex-col',
        'flex-1 min-h-0 h-full' => $contain,
    ]) }}>
        <!-- main content -->
        {{ $slot }}
    </div>
</div>
