<div class="leading-tight bg-gradient-to-b from-cod-gray-500 _via-cod-gray-200 to-cod-gray-200 dark:from-cod-gray-800 dark:via-cod-gray-700/60 dark:to-cod-gray-900 w-full flex min-h-0 flex-1 flex-col overflow-hidden">
    <div {{ $attributes->merge(['class' => 'flex flex-1 flex-col min-h-0 h-full']) }}>
        <!-- main content -->
        {{ $slot }}
    </div>
</div>