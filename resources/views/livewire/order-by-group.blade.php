<div class="overflow-x-auto" data-ribbon-view="group">
    <x-ribbon ob="group">
    @foreach ($games as $game)
        <swiper-slide class="relative">
            <div x-show="skeletonGroup" class="absolute inset-0 z-10 flex items-start justify-center py-4">
                <div class="group relative flex h-full flex-col overflow-hidden rounded-xl border-2 border-cod-gray-300 bg-cod-gray-200/80 shadow dark:border-cod-gray-950 dark:bg-cod-gray-900/90">
                    <div class="flex h-full w-[230px] shrink-0 flex-col overflow-hidden">
                        <div class="relative h-[177px] w-full shrink-0 skeleton">
                            <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-transparent/30 via-transparent to-cod-gray-200 dark:from-transparent dark:via-transparent dark:to-cod-gray-900"></div>
                        </div>
                        <div class="flex min-h-0 flex-1 flex-col items-center justify-center gap-1.5 bg-cod-gray-200/80 px-2 py-2 dark:bg-cod-gray-900/90">
                            <div class="h-2.5 w-[78%] max-w-full rounded-full bg-cod-gray-600/60 dark:bg-cod-gray-600/80 skeleton"></div>
                            <div class="h-2.5 w-[62%] max-w-full rounded-full bg-cod-gray-600/50 dark:bg-cod-gray-600/70 skeleton"></div>
                            <div class="h-2.5 w-[44%] max-w-full rounded-full bg-cod-gray-600/45 dark:bg-cod-gray-600/60 skeleton"></div>
                        </div>
                    </div>
                </div>
            </div>
            <a
               href="{{ $this->gameRoute($game) }}"
               @click="$dispatch('loader-top-on')"
               :class="skeletonGroup ? 'invisible pointer-events-none' : ''"
               class="relative z-0 lazy-load-container"
               data-loaded="false"
            >
                <livewire:game-card :game="$game" :show-console-label="false" :key="$game->id" />
            </a>
        </swiper-slide>
    @endforeach
    </x-ribbon>
</div>
