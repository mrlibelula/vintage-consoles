<div class="flex w-full min-w-0 flex-col gap-y-4 py-4">
    @foreach ($games as $game)
    <a wire:navigate href="{{ $this->gameRoute($game) }}" @click="$dispatch('loader-top-on')" class="group relative z-0 flex min-w-0 w-full items-center text-left justify-start gap-x-4 lg:gap-x-6 cursor-pointer rounded-md dark:hover:bg-cod-gray-800/60 hover:shadow-md shadow-cod-gray-300 dark:shadow-black smooth-300 2xl:pr-6">
        <!-- game poster -->
        <div class="w-[5rem] h-[5.5rem] shrink-0 rounded-md border-[3px] border-cod-gray-300 dark:border-cod-gray-600 shadow-md shadow-cod-gray-500 dark:shadow-black overflow-hidden brightness-75 group-hover:brightness-100 group-hover:scale-110 smooth-300">
            <x-game-poster
                :src="$game->poster"
                :alt="$game->title"
                container-class="h-full w-full"
                loading-targets="previousPage,nextPage,gotoPage,updatedPage"
            />
        </div>
        <!-- game data -->
        <div class="flex min-w-0 flex-1 justify-start items-start gap-x-4">
            <div class="min-w-0 flex-1 text-lg md:text-xl">
                <div class="tracking-tight md:text-[1.35rem] text-cod-gray-800 dark:text-cod-gray-100 group-hover:text-rose-700 dark:group-hover:text-rose-300 smooth-300 leading-none truncate">
                    {{ $game->title }}
                </div>
                <div class="text-cod-gray-600 dark:text-cod-gray-300 leading-none truncate">
                    {{ $game->publisher }}
                </div>
                <div class="leading-none">
                    {{ $game->release_year }}
                    @if($game->rating)
                    <span class="md:hidden text-emerald-700/80 dark:text-emerald-500 leading-none">
                        {{ number_format($game->rating * 100, 0) }}%
                    </span>
                    @endif
                </div>
            </div>
            @if($game->rating)
            <div class="hidden md:block shrink-0 text-emerald-700/80 dark:text-emerald-500 group-hover:text-emerald-400 smooth-300 leading-none px-2">
                {{ number_format($game->rating * 100, 0) }}%
            </div>
            @endif
        </div>
    </a>
    @endforeach

    {{ $games->links() }}
</div>
