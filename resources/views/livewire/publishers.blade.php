<div>
    <x-slot name="header">
        <div class="flex flex-col-reverse md:flex-row gap-y-4 md:gap-y-0 items-center gap-x-10 justify-between">
            <div>
                @if ($publisher_name)
                <div class=" tracking-wider text-2xl md:text-3xl text-lime-600 dark:text-lime-500">
                    {{ $publisher_name ?? 'n/a' }} <span class="tracking-wider text-2xl md:text-3xl text-cod-gray-700 dark:text-cod-gray-200">games <span class=" text-md text-cod-gray-600">@if(count($filtered_games))({{ count($filtered_games) }})@endif</span></span>
                </div>
                @else
                <div class=" tracking-wider text-2xl md:text-3xl">
                    Publishers
                </div>
                @endif
            </div>
            <x-explore-buttons />
        </div>
        
    </x-slot>
    <x-container class="mt-6">
        <div class="flex flex-col gap-y-10 text-cod-gray-700 dark:text-cod-gray-400">
            @if ($publisher_name)
                <!-- game list display options -->
                <div class="flex items-center justify-start gap-x-3 w-full dark:text-cod-gray-500 leading-none -mb-8">
                    <a @click="$dispatch('skeleton-square-off'); $dispatch('skeleton-group-on')" wire:navigate href="/games/publishers/{{ $publisher_name }}?ob=group" class="btn-small"><x-icons.group class="{{ $ob === 'group' ? 'text-gray-200' : '' }}" /></a>
                    <a @click="$dispatch('skeleton-group-off'); $dispatch('skeleton-square-on')" wire:navigate href="/games/publishers/{{ $publisher_name }}?ob=squares" class="btn-small"><x-icons.squares class="{{ $ob === 'squares' ? 'text-gray-200' : '' }}" /></a>
                </div>
                <!-- filtered results ribbon -->
                <div 
                    x-data="{ 
                        skeletonSquare: false,
                        skeletonGroup: false,
                    }" 
                    @skeleton-square-off.window="skeletonSquare = false"
                    @skeleton-square-on.window="skeletonSquare = true"

                    @skeleton-group-off.window="skeletonGroup = false"
                    @skeleton-group-on.window="skeletonGroup = true"
                >
                    <div class="w-full min-w-0 py-4">
                        <x-ribbon :customSlidesPerView="[
                            'sm' => 2,
                            'md' => 3,
                            'xl' => 4,
                        ]">
                            @foreach ($filtered_games as $game)
                            <swiper-slide class="py-4">
                                @if ($ob === 'group')
                                    {{-- Group skeleton --}}
                                    <div x-show="skeletonGroup" class="flex h-[calc(290px+2rem)] shrink-0 items-start justify-start py-4">
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
                                    {{-- Group real card --}}
                                    <a x-show="!skeletonGroup"
                                       href="{{ route('play', ['console_short_name' => $game['console_short_name'], 'game_title_slug' => $game['slug']]) }}"
                                       @click="$dispatch('loader-top-on');"
                                       class="lazy-load-container" data-loaded="false"
                                    >
                                        <livewire:game-card :game="$game" :key="$game['id']" />
                                    </a>
                                @else
                                    {{-- Squares skeleton --}}
                                    <div x-show="skeletonSquare" class="h-[12rem] flex my-2 justify-center w-full">
                                        <div class="group shrink-0 rounded-lg overflow-hidden border-[3px] border-cod-gray-500 bg-gradient-to-br from-cod-gray-700 via-cod-gray-700/50 to-cod-gray-800 opacity-75 shadow-md shadow-cod-gray-500 animate-pulse dark:border-cod-gray-700 dark:shadow-black dark:from-cod-gray-800 dark:via-cod-gray-800/50 dark:to-cod-gray-900">
                                            <div class="pointer-events-none h-[12rem] w-[8.5rem] bg-gradient-to-b from-cod-gray-600/45 via-cod-gray-700/55 to-cod-gray-900/85 dark:from-cod-gray-700/50 dark:via-cod-gray-800/60 dark:to-black/35"></div>
                                        </div>
                                    </div>
                                    {{-- Squares real card --}}
                                    <a x-show="!skeletonSquare"
                                       href="{{ route('play', ['console_short_name' => $game['console_short_name'], 'game_title_slug' => $game['slug']]) }}"
                                       @click="$dispatch('loader-top-on');"
                                       class="h-[12rem] flex my-2 justify-center w-full lazy-load-container" data-loaded="false"
                                    >
                                        <livewire:game-card-classic :game="$game" :key="$game['id']" />
                                    </a>
                                @endif
                            </swiper-slide>
                            @endforeach
                        </x-ribbon>
                    </div>
                </div>
            @endif

            <!-- all publishers list -->
            <div>
                Here are all the <span class=" text-lime-700 dark:text-lime-500">publishers</span> detected on the database:
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-6 gap-y-[0.2rem]">
                <!-- publishers list -->
                @forelse ($publishers as $publisher)
                <div class="flex gap-x-1">
                    <a @click="$dispatch('loader-top-on')" wire:navigate href="{{ route('publishers', $publisher['name']) }}" class="link capitalize leading-none mb-4">
                        {{ $publisher['name'] }}
                    </a>
                    <span class="text-sm mt-0.5 text-gray-500">({{ $publisher['games_count'] }})</span>
                </div>
                @empty
                <div>
                    No Publishers found
                </div>
                @endforelse
            </div>
        </div>
    </x-container>

</div>
