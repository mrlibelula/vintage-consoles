<div class="overflow-x-auto">
    <x-ribbon ob="squares">
        @foreach ($selected_console['games'] as $game)
        <swiper-slide>
            {{-- Skeleton: occupies the exact same Swiper slot, shown while skeletonSquare is active --}}
            <div x-show="skeletonSquare" class="h-[12rem] flex my-2 justify-center w-full">
                <div class="group shrink-0 rounded-lg overflow-hidden border-[3px] border-cod-gray-500 bg-gradient-to-br from-cod-gray-700 via-cod-gray-700/50 to-cod-gray-800 opacity-75 shadow-md shadow-cod-gray-500 animate-pulse dark:border-cod-gray-700 dark:shadow-black dark:from-cod-gray-800 dark:via-cod-gray-800/50 dark:to-cod-gray-900">
                    <div class="pointer-events-none h-[12rem] w-[8.5rem] bg-gradient-to-b from-cod-gray-600/45 via-cod-gray-700/55 to-cod-gray-900/85 dark:from-cod-gray-700/50 dark:via-cod-gray-800/60 dark:to-black/35"></div>
                </div>
            </div>
            {{-- Real card: shown when skeleton is off --}}
            <a x-show="!skeletonSquare"
               href="{{ $this->gameRoute($game) }}"
               @click="$dispatch('loader-top-on')"
               class="h-[12rem] flex my-2 justify-center w-full lazy-load-container"
               data-loaded="false"
            >
                <livewire:game-card-classic :game="$game" :key="$game['id']" />
            </a>
        </swiper-slide>
        @endforeach
    </x-ribbon>
</div>
