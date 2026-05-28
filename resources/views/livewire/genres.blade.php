<div>
    <x-slot name="header">
        <div class="flex flex-col-reverse md:flex-row gap-y-4 md:gap-y-0 items-center gap-x-10 justify-between">
            <div>
                @if ($genre_name)
                <div class="tracking-wider text-2xl md:text-3xl text-sky-500">
                    #{{ $genre_name ?? 'n/a' }} <span class="tracking-wider text-2xl md:text-3xl text-cod-gray-700 dark:text-cod-gray-200">games <span class="text-md text-cod-gray-600">@if(count($filtered_games))({{ count($filtered_games) }})@endif</span></span>
                </div>
                @else
                <div class="tracking-wider text-2xl md:text-3xl">Genres</div>
                @endif
            </div>
            <x-explore-buttons />
        </div>
    </x-slot>

    <x-container class="mt-6">
        <div class="flex flex-col gap-y-10 text-cod-gray-700 dark:text-cod-gray-400">
            @if ($genre_name)
                <!-- game list display options -->
                <div class="flex items-center justify-between gap-x-3 w-full dark:text-cod-gray-500 leading-none -mb-8">
                    <div class="flex items-center gap-x-3">
                        <a
                            @click="$dispatch('skeleton-square-off'); $dispatch('skeleton-group-on')"
                            wire:navigate
                            href="/games/genres/{{ $genre_name }}?ob=group"
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
                            href="/games/genres/{{ $genre_name }}?ob=squares"
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
                    wire:key="genre-games-{{ $gameSortField }}-{{ $gameSortDirection }}-{{ $ob }}"
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
                            :key="'genre-ribbon-'.$genre_name.'-'.$ob.'-'.$gameSortField.'-'.$gameSortDirection"
                        />
                    </div>
                </div>
            @endif

            <!-- all genres list -->
            <div class="flex flex-col sm:flex-row items-start justify-between gap-3">
                <p class="min-w-0 flex-1 pr-2 text-cod-gray-700 dark:text-cod-gray-400 m-0 text-sm leading-snug sm:text-base">
                    Here are all the <span class="text-sky-600 dark:text-sky-400">genres</span> detected on the database:
                </p>
                <div
                    class="inline-flex shrink-0 rounded-md border border-cod-gray-400/60 p-0.5 text-[0.65rem] leading-none tracking-wide text-cod-gray-700 shadow-sm dark:border-cod-gray-600 dark:text-cod-gray-200 dark:shadow-black sm:text-xs"
                    role="group"
                    aria-label="Sort genres"
                >
                    <button type="button" wire:click="setGenreSort('count')" @click="$dispatch('loader-top-on')"
                        class="rounded px-2 py-1 font-sans transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 {{ $genreSort === 'count' ? 'bg-sky-600 text-white dark:bg-sky-500' : 'hover:bg-cod-gray-300/60 dark:hover:bg-cod-gray-700' }}">
                        Count
                    </button>
                    <button type="button" wire:click="setGenreSort('alpha')" @click="$dispatch('loader-top-on')"
                        class="rounded px-2 py-1 font-sans transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 {{ $genreSort === 'alpha' ? 'bg-sky-600 text-white dark:bg-sky-500' : 'hover:bg-cod-gray-300/60 dark:hover:bg-cod-gray-700' }}">
                        A–Z
                    </button>
                </div>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-6 gap-y-[0.2rem]" wire:key="genre-grid-{{ $genreSort }}">
                @forelse ($genres as $genre)
                <div class="flex gap-x-1">
                    <a @click="$dispatch('loader-top-on')" wire:navigate href="{{ route('genres', $genre->name) }}" class="link leading-none mb-4">
                        #{{ $genre->name }}
                    </a>
                    <div class="text-sm text-gray-500 mt-0.5">({{ $genre->games_count }})</div>
                </div>
                @empty
                <div>No genres found</div>
                @endforelse
            </div>
        </div>
    </x-container>
</div>
