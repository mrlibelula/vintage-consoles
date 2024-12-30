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
                    <a @click="$dispatch('loader-top-on'); $dispatch('skeleton-group-on')" wire:navigate href="/game/publishers/{{ $publisher_name }}?ob=group" class="btn-small"><x-icons.group class="{{ $ob === 'group' ? 'text-gray-200' : '' }}" /></a>
                    <a @click="$dispatch('loader-top-on'); $dispatch('skeleton-square-on')" wire:navigate href="/game/publishers/{{ $publisher_name }}?ob=squares" class="btn-small"><x-icons.squares class="{{ $ob === 'squares' ? 'text-gray-200' : '' }}" /></a>
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
                    <div class="flex flex-no-wrap gap-x-4">
                        <!-- skeleton group -->
                        <template x-if="skeletonGroup">
                            <x-skeleton-group :count="4" />
                        </template>
                        
                        <!-- skeleton square -->
                        <template x-if="skeletonSquare">                        
                            <x-skeleton-square :count="4" />
                        </template>

                        <x-ribbon :customSlidesPerView="[
                            'sm' => 2,
                            'md' => 3,
                            'xl' => 4,
                        ]">
                            @foreach ($filtered_games as $game)
                            <swiper-slide class="py-4">
                                <a 
                                    href="{{ route('play', [
                                        'console_short_name' => $game['console_short_name'],
                                        'game_title_slug' => $game['slug']
                                    ]) }}"
                                    class="lazy-load-container" data-loaded="false"
                                >
                                    <div :class="{ 'hidden': skeletonGroup || skeletonSquare }" class=" h-full">
                                        @if ($ob === 'group')
                                        <livewire:game-card :game="$game" :key="$game['id']" :key="uniqid()" />
                                        @elseif($ob === 'squares')
                                        <livewire:game-card-classic :game="$game" :key="$game['id']" class="p-4" :key="uniqid()" />
                                        @endif
                                    </div>
                                </a>
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
                <a @click="$dispatch('loader-top-on')" wire:navigate href="{{ route('publishers', $publisher) }}" class="link capitalize">
                    {{ $publisher }}
                </a>
                @empty
                <div>
                    No Publishers found
                </div>
                @endforelse
            </div>
        </div>
    </x-container>

</div>
