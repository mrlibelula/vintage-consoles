@props(['contain' => true])

<div @class([
    'leading-tight bg-gradient-to-b from-cod-gray-500 _via-cod-gray-200 to-cod-gray-200 dark:from-cod-gray-800 dark:via-cod-gray-700/60 dark:to-cod-gray-900 w-full flex flex-col',
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
