<button class="lazy-load-bg group rounded-lg overflow-hidden bg-center bg-cover bg-no-repeat border-[3px] shadow-md shadow-cod-gray-500 dark:shadow-black  border-cod-gray-500 dark:border-cod-gray-700 opacity-75 hover:brightness-110 smooth-300 cursor-pointer" 
    data-bg-url="{{ $game['poster'] }}" 
    style="background-image: url({{ asset('images/placeholder-poster-homer.jpg') }});"
>
    <div class="flex items-end justify-center w-[8.5rem] h-[12rem] overflow-hidden">
        @isset($game['console_short_name'])
        <div class=" w-full bg-white/70 dark:bg-cod-gray-700/70 dark:text-gray-200 font-mono text-xs py-0.5 font-semibold">
            {{ $game['console_short_name'] }}
        </div>
        @else
        &nbsp;
        @endisset
    </div>
</button>
