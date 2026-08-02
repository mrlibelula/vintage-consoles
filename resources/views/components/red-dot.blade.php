<div {{ $attributes->merge(['class' => 'relative inline-flex shrink-0 items-center justify-center w-1.5 h-1.5 md:w-2 md:h-2']) }}>
    @if ($beaming)
        <span class="absolute inset-0 rounded-full bg-rose-400 opacity-75 animate-ping"></span>
        <span class="relative w-full h-full rounded-full bg-rose-700"></span>
    @else
        <span class="relative w-full h-full rounded-full bg-cod-gray-500 dark:bg-cod-gray-600"></span>
    @endif
</div>
