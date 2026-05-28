<div class="overflow-x-auto" data-ribbon-view="squares">
    <x-ribbon ob="squares">
        @foreach ($games as $game)
        <swiper-slide class="relative">
            <div x-show="skeletonSquare" class="absolute inset-0 z-10 flex items-center justify-center">
                <div class="group shrink-0 rounded-lg overflow-hidden border-[3px] border-cod-gray-500 bg-gradient-to-br from-cod-gray-700 via-cod-gray-700/50 to-cod-gray-800 opacity-75 shadow-md shadow-cod-gray-500 animate-pulse dark:border-cod-gray-700 dark:shadow-black dark:from-cod-gray-800 dark:via-cod-gray-800/50 dark:to-cod-gray-900">
                    <div class="pointer-events-none h-[12rem] w-[8.5rem] bg-gradient-to-b from-cod-gray-600/45 via-cod-gray-700/55 to-cod-gray-900/85 dark:from-cod-gray-700/50 dark:via-cod-gray-800/60 dark:to-black/35"></div>
                </div>
            </div>
            <a
               href="{{ $this->gameRoute($game) }}"
               @click="$dispatch('loader-top-on')"
               :class="skeletonSquare ? 'invisible pointer-events-none' : ''"
               class="relative z-0 flex h-[12rem] w-full shrink-0 items-center justify-center my-2 lazy-load-container"
               data-loaded="false"
            >
                <livewire:game-card-classic :game="$game" :show-console-label="false" :key="$game->id" />
            </a>
        </swiper-slide>
        @endforeach
    </x-ribbon>
</div>
