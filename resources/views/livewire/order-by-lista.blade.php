<div class="flex flex-col gap-y-4 mt-4">
    @foreach ($selected_console['games'] as $game)
    <div class="group flex items-center justify-start gap-x-4 lg:gap-x-6 cursor-pointer rounded-md dark:hover:bg-cod-gray-800/60 hover:shadow-md shadow-black transition duration-300 ease-in-out">
        <!-- game poster -->
        <div class="w-[5rem] h-[5.5rem] rounded-md border-[3px] border-cod-gray-600 shadow-md shadow-black overflow-hidden group-hover:brightness-125 group-hover:scale-110 transition duration-300 ease-in-out">
            <img class="w-full h-full" src="{{ $game['poster'] }}" alt="{{ $game['title'] }}">
        </div>
        <!-- game data -->
        <div class="flex justify-start items-start gap-x-4 w-full">
            <!-- game info -->
            <div class="w-full text-lg md:text-xl">
                <div class="tracking-tight md:text-[1.35rem] text-cod-gray-100 leading-none group-hover:text-rose-300 transition duration-300 ease-in-out">
                    {{ $game['title'] }}
                </div>
                <div class=" text-cod-gray-300 leading-none">
                    {{ $game['publisher'] }}
                </div>
                <div class="leading-none">
                    {{ $game['release_year'] }}
                    <span class=" md:hidden text-emerald-500 leading-none">
                        {{ number_format($game['rating'] * 100, 0) }}%
                    </span>
                </div>
            </div>
            <!-- game rating -->
            <div class=" hidden md:block text-emerald-500 group-hover:text-emerald-400 transition duration-300 ease-in-out leading-none px-4">
                {{ number_format($game['rating'] * 100, 0) }}%
            </div>
        </div>
    </div>
    @endforeach
</div>