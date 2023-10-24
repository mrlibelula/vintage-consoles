<div class="flex flex-col gap-y-4 mt-4">
    @foreach ($games as $game)
    <a wire:navigate href="{{ $this->gameRoute($game) }}" class="group flex items-center text-left justify-start gap-x-4 lg:gap-x-6 cursor-pointer rounded-md dark:hover:bg-cod-gray-800/60 hover:shadow-md shadow-cod-gray-300 dark:shadow-black smooth-300">
        <!-- game poster -->
        <div class="w-[5rem] h-[5.5rem] rounded-md border-[3px] border-cod-gray-300 dark:border-cod-gray-600 shadow-md shadow-cod-gray-500 dark:shadow-black overflow-hidden brightness-75 group-hover:brightness-100 group-hover:scale-110 smooth-300">
            <img class="w-full h-full" src="{{ $game['poster'] }}" alt="{{ $game['title'] }}">
        </div>
        <!-- game data -->
        <div class="flex justify-start items-start gap-x-4 w-full">
            <!-- game info -->
            <div class="w-full text-lg md:text-xl">
                <div class="tracking-tight md:text-[1.35rem] text-cod-gray-800 dark:text-cod-gray-100 group-hover:text-rose-700  dark:group-hover:text-rose-300 smooth-300 leading-none">
                    {{ $game['title'] }}
                </div>
                <div class=" text-cod-gray-600 dark:text-cod-gray-300 leading-none">
                    {{ $game['publisher'] }}
                </div>
                <div class=" leading-none">
                    {{ $game['release_year'] }}
                    <span class=" md:hidden text-emerald-700/80 dark:text-emerald-500 leading-none">
                        {{ number_format($game['rating'] * 100, 0) }}%
                    </span>
                </div>
            </div>
            <!-- game rating -->
            <div class=" hidden md:block text-emerald-700/80 dark:text-emerald-500 group-hover:text-emerald-400 smooth-300 leading-none px-4">
                {{ number_format($game['rating'] * 100, 0) }}%
            </div>
        </div>
    </a>
    @endforeach

    {{ $games->links() }}

</div>