<div>
    <x-slot name="header">
        <div class="flex items-center gap-x-10 justify-between">
            <div>
                @if ($genre_name)
                <div class=" tracking-wider text-2xl md:text-3xl text-sky-500">
                    #{{ $genre_name ?? 'n/a' }} <span class="tracking-wider text-2xl md:text-3xl text-cod-gray-700 dark:text-cod-gray-200">games <span class=" text-md text-cod-gray-600">@if(count($filtered_games))({{ count($filtered_games) }})@endif</span></span>
                </div>
                @else
                <div class=" tracking-wider text-2xl md:text-3xl">
                    Genres
                </div>
                @endif
            </div>
            <x-explore-buttons />
        </div>
        
    </x-slot>
    <x-container class="mt-6">
        <div class="flex flex-col gap-y-10 text-cod-gray-700 dark:text-cod-gray-400">
            @if ($genre_name)
                <!-- game list display options -->
                <div class="flex items-center justify-start gap-x-3 w-full dark:text-cod-gray-500 leading-none -mb-8">
                    <button @click="$dispatch('loader-top-on'); $dispatch('skeleton-group-on')" wire:click="orderBy('group')" class="btn-small"><x-icons.group class="{{ $order_by['group'] ? 'text-gray-200' : '' }}" /></button>
                    <button @click="$dispatch('loader-top-on'); $dispatch('skeleton-square-on')" wire:click="orderBy('squares')" class="btn-small"><x-icons.squares class="{{ $order_by['squares'] ? 'text-gray-200' : '' }}" /></button>
                </div>
                <!-- filtered results ribbon -->
                <x-ribbon 
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
                            <x-skeleton-group :count="20" />
                        </template>
                        
                        <!-- skeleton square -->
                        <template x-if="skeletonSquare">                        
                            <x-skeleton-square :count="20" />
                        </template>

                        @foreach ($filtered_games as $game)
                        <a wire:navigate href="{{ route('play', [
                                \App\Service\Tool::encode($game['id']),
                                $game['console_short_name'], 
                                $game['title'],
                            ]) }}"
                        >
                            <div :class="{ 'hidden': skeletonGroup || skeletonSquare }" class=" h-full">
                                @if ($order_by['group'])
                                <livewire:game-card :game="$game" :key="$game['id']" :key="uniqid()" />
                                @elseif($order_by['squares'])
                                <livewire:game-card-classic :game="$game" :key="$game['id']" class="p-4" :key="uniqid()" />
                                @endif
                            </div>
                        </a>
                        @endforeach
                    </div>
                </x-ribbon>
            @endif

            <!-- all genres list -->
            <div>
                Here are all the <span class=" font-semibold text-gray-800 dark:text-gray-300">genres</span> detected on the database:
            </div>
            <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-[0.2rem]">
                <!-- genres list -->
                @forelse ($genres as $genre)
                <a @click="$dispatch('loader-top-on')" wire:navigate href="{{ route('genres', $genre) }}" class="link">
                    #{{ $genre }}
                </a>
                @empty
                <div>
                    No genres found
                </div>
                @endforelse
            </div>
        </div>
    </x-container>

</div>
