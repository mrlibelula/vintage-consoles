<div class="leading-tight bg-gradient-to-b from-cod-gray-500 _via-cod-gray-200 to-cod-gray-200 dark:from-cod-gray-800 dark:via-cod-gray-700/60 dark:to-cod-gray-900 w-full h-[44vh]">
    <div {{ $attributes->merge(['class' => 'h-full overflow-hidden overflow-y-auto']) }}>
        <!-- main content -->
        {{ $slot }}
    </div>
</div>