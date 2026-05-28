<div>
    <x-slot name="header">
        <div class="flex flex-col-reverse md:flex-row gap-y-4 md:gap-y-0 items-center gap-x-10 justify-between">
            <div>
                @if ($publisher_name)
                <div class="tracking-wider text-2xl md:text-3xl text-lime-600 dark:text-lime-500">
                    {{ $publisher_name ?? 'n/a' }} <span class="tracking-wider text-2xl md:text-3xl text-cod-gray-700 dark:text-cod-gray-200">games <span class="text-md text-cod-gray-600">@if(count($filtered_games))({{ count($filtered_games) }})@endif</span></span>
                </div>
                @else
                <div class="tracking-wider text-2xl md:text-3xl">Publishers</div>
                @endif
            </div>
            <x-explore-buttons />
        </div>
    </x-slot>

    <x-container class="mt-6">
        <div class="flex flex-col gap-y-10 text-cod-gray-700 dark:text-cod-gray-400">
            @if ($publisher_name)
                <!-- game list display options -->
                <div class="flex items-center justify-between gap-x-3 w-full dark:text-cod-gray-500 leading-none -mb-8">
                    <div class="flex items-center gap-x-3">
                        <a
                            @click="$dispatch('skeleton-square-off'); $dispatch('skeleton-group-on')"
                            wire:navigate
                            href="/games/publishers/{{ $publisher_name }}?ob=group"
                            class="btn-pixel pixel-tooltip"
                            data-pixel-tooltip="Preview cards"
                            aria-label="Preview cards"
                            aria-current="{{ $ob === 'group' ? 'page' : 'false' }}"
                        >
                            <x-pixelarticon
                                name="gallery-thumbnails"
                                :size="24"
                                @class(['pixel-icon-rose' => $ob === 'group'])
                            />
                        </a>
                        <a
                            @click="$dispatch('skeleton-group-off'); $dispatch('skeleton-square-on')"
                            wire:navigate
                            href="/games/publishers/{{ $publisher_name }}?ob=squares"
                            class="btn-pixel pixel-tooltip"
                            data-pixel-tooltip="Poster view"
                            aria-label="Poster view"
                            aria-current="{{ $ob === 'squares' ? 'page' : 'false' }}"
                        >
                            <x-pixelarticon
                                name="grid-2x2-2"
                                :size="24"
                                @class(['pixel-icon-rose' => $ob === 'squares'])
                            />
                        </a>
                    </div>
                    <x-game-carousel-sort
                        :sort-field="$gameSortField"
                        :sort-direction="$gameSortDirection"
                    />
                </div>
                <!-- filtered results ribbon -->
                <div
                    wire:key="publisher-games-{{ $gameSortField }}-{{ $gameSortDirection }}-{{ $ob }}"
                    x-data="{
                        skeletonSquare: {{ $ob === 'squares' ? 'true' : 'false' }},
                        skeletonGroup: {{ $ob === 'group' ? 'true' : 'false' }},
                    }"
                    @skeleton-square-off.window="skeletonSquare = false"
                    @skeleton-square-on.window="skeletonSquare = true"
                    @skeleton-group-off.window="skeletonGroup = false"
                    @skeleton-group-on.window="skeletonGroup = true"
                    @ribbon-skeleton-clear.window="
                        const m = $event.detail?.mode;
                        if (m === 'group') skeletonGroup = false;
                        if (m === 'squares') skeletonSquare = false;
                    "
                >
                    <div class="w-full min-w-0 py-4">
                        <livewire:filtered-games-ribbon
                            :game-ids="collect($filtered_games)->pluck('id')->map(fn ($v) => (int) $v)->all()"
                            :ob="$ob"
                            :key="'publisher-ribbon-'.$publisher_name.'-'.$ob.'-'.$gameSortField.'-'.$gameSortDirection"
                        />
                    </div>
                </div>
            @endif

            <!-- all publishers list -->
            <div>
                Here are all the <span class="text-lime-700 dark:text-lime-500">publishers</span> detected on the database:
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-6 gap-y-[0.2rem]">
                @forelse ($publishers as $publisher)
                <div class="flex gap-x-1">
                    <a @click="$dispatch('loader-top-on')" wire:navigate href="{{ route('publishers', $publisher['name']) }}" class="link capitalize leading-none mb-4">
                        {{ $publisher['name'] }}
                    </a>
                    <span class="text-sm mt-0.5 text-gray-500">({{ $publisher['games_count'] }})</span>
                </div>
                @empty
                <div>No Publishers found</div>
                @endforelse
            </div>
        </div>
    </x-container>
</div>
