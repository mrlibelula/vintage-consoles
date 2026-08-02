<div data-ribbon-view="{{ $ob }}">
    <x-ribbon
        :ob="$ob"
        :customSlidesPerView="$ob === 'squares'
            ? ['sm' => 2, 'md' => 3, 'xl' => 5]
            : ['sm' => 1, 'md' => 2, 'xl' => 4]
        "
    >
        @foreach ($games as $game)
            <swiper-slide class="relative {{ $ob === 'squares' ? 'py-0' : '' }}">
                @if ($ob === 'group')
                    <div x-show="skeletonGroup" class="absolute inset-0 z-10 flex w-full items-start py-4">
                        <div class="group relative aspect-square w-full overflow-hidden rounded-xl border-2 border-cod-gray-500 bg-cod-gray-200/80 shadow dark:border-cod-gray-700/50 dark:bg-cod-gray-900/90">
                            <div class="absolute inset-0 skeleton">
                                <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-cod-gray-200 dark:to-cod-gray-900"></div>
                            </div>
                            <div class="absolute inset-x-0 bottom-0 z-10 flex flex-col items-center justify-center gap-1.5 px-2 pb-3 pt-8">
                                <div class="h-2.5 w-[78%] max-w-full rounded-full bg-cod-gray-600/60 dark:bg-cod-gray-600/80 skeleton"></div>
                                <div class="h-2.5 w-[62%] max-w-full rounded-full bg-cod-gray-600/50 dark:bg-cod-gray-600/70 skeleton"></div>
                            </div>
                        </div>
                    </div>
                    <a
                        href="{{ route('play', ['console_short_name' => $game->console->short_name, 'game_title_slug' => $game->slug]) }}"
                        @click="$dispatch('loader-top-on')"
                        :class="skeletonGroup ? 'invisible pointer-events-none' : ''"
                        class="relative z-0 lazy-load-container"
                        data-loaded="false"
                    >
                        <livewire:game-card :game="$game" :key="'fg-card-'.$game->id" />
                    </a>
                @else
                    <div x-show="skeletonSquare" class="absolute inset-0 z-10 flex w-full items-center">
                        <div class="group w-full min-w-0 rounded-lg overflow-hidden border-[3px] border-cod-gray-500 bg-gradient-to-br from-cod-gray-700 via-cod-gray-700/50 to-cod-gray-800 opacity-75 shadow-md shadow-cod-gray-500 animate-pulse dark:border-cod-gray-700 dark:shadow-black dark:from-cod-gray-800 dark:via-cod-gray-800/50 dark:to-cod-gray-900">
                            <div class="pointer-events-none w-full aspect-[8.5/12] bg-gradient-to-b from-cod-gray-600/45 via-cod-gray-700/55 to-cod-gray-900/85 dark:from-cod-gray-700/50 dark:via-cod-gray-800/60 dark:to-black/35"></div>
                        </div>
                    </div>
                    <a
                        href="{{ route('play', ['console_short_name' => $game->console->short_name, 'game_title_slug' => $game->slug]) }}"
                        @click="$dispatch('loader-top-on')"
                        :class="skeletonSquare ? 'invisible pointer-events-none' : ''"
                        class="relative z-0 flex w-full min-w-0 shrink-0 items-center my-2 lazy-load-container"
                        data-loaded="false"
                    >
                        <livewire:game-card-classic :game="$game" :key="'fg-classic-'.$game->id" class="p-4" />
                    </a>
                @endif
            </swiper-slide>
        @endforeach
    </x-ribbon>
</div>

