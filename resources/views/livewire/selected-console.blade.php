<div class="flex flex-col gap-y-6 w-full bg-cod-gray-600/20_ bg-[#3f3f3f]_ bg-white/50 dark:bg-black/50 p-0 lg:p-2
    {{ $is_selected_tab_first ? 'rounded-b-xl rounded-tr-xl' : ($is_selected_tab_last ? 'rounded-b-xl rounded-tl-xl' : 'rounded-xl') }}
">
    <div class="flex flex-col-reverse xl:flex-row items-start justify-between gap-x-10 w-full">
        <!-- main -->
        <div class="w-full flex flex-col gap-y-8 items-start justify-start p-4">

            <div class="w-full flex flex-col xl:flex-row gap-y-8 gap-x-[4rem] items-start justify-start">

                <!-- main content -->
                <div
                    x-data="{
                        skeletonSquare: {{ $ob === 'squares' ? 'true' : 'false' }},
                        skeletonGroup: {{ $ob === 'group' ? 'true' : 'false' }},
                        skeletonLista: false,
                    }"
                    @skeleton-square-off.window="skeletonSquare = false"
                    @skeleton-square-on.window="skeletonSquare = true"
                    @skeleton-group-off.window="skeletonGroup = false"
                    @skeleton-group-on.window="skeletonGroup = true"
                    @skeleton-lista-off.window="skeletonLista = false"
                    @skeleton-lista-on.window="skeletonLista = true"
                    @ribbon-skeleton-clear.window="
                        const m = $event.detail?.mode;
                        if (m === 'group') skeletonGroup = false;
                        if (m === 'squares') skeletonSquare = false;
                    "
                    class="flex flex-col gap-y-8 w-full xl:w-[70%] main-container">

                    <!-- game list display options -->
                    <div class="flex items-center justify-between gap-x-3 w-full dark:text-cod-gray-500 leading-none -mb-8">
                        <div class="flex items-center gap-x-3">
                            <a
                                @click="$dispatch('skeleton-square-off'); $dispatch('skeleton-group-on');"
                                wire:navigate
                                href="/{{ $selected_console->short_name }}?ob=group"
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
                                @click="$dispatch('skeleton-group-off'); $dispatch('skeleton-square-on');"
                                wire:navigate
                                href="/{{ $selected_console->short_name }}?ob=squares"
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
                            <a
                                @click="$dispatch('loader-top-on'); $dispatch('skeleton-lista-on');"
                                wire:navigate
                                href="/{{ $selected_console->short_name }}?ob=lista"
                                class="btn-pixel pixel-tooltip"
                                data-pixel-tooltip="List view"
                                aria-label="List view"
                                aria-current="{{ $ob === 'lista' ? 'page' : 'false' }}"
                            >
                                <x-pixelarticon
                                    name="bulletlist"
                                    :size="24"
                                    @class(['pixel-icon-rose' => $ob === 'lista'])
                                />
                            </a>
                        </div>
                        <x-game-carousel-sort
                            :sort-field="$gameSortField"
                            :sort-direction="$gameSortDirection"
                        />
                    </div>

                    <!-- skeleton lista -->
                    <template x-if="skeletonLista">
                        <div class="py-4 mb-[0.9rem]">
                            <x-skeleton-lista />
                        </div>
                    </template>

                    <div :class="{ 'hidden': skeletonLista }" class="w-full min-w-0 py-4">
                        @if ($ob === 'group')
                        <livewire:order-by-group
                            :selected_console="$selected_console"
                            :game-sort-field="$gameSortField"
                            :game-sort-direction="$gameSortDirection"
                            :key="'group-'.$gameSortField.'-'.$gameSortDirection.'-'.$selected_console->id"
                        />
                        @elseif ($ob === 'squares')
                        <livewire:order-by-squares
                            :selected_console="$selected_console"
                            :game-sort-field="$gameSortField"
                            :game-sort-direction="$gameSortDirection"
                            :key="'squares-'.$gameSortField.'-'.$gameSortDirection.'-'.$selected_console->id"
                        />
                        @elseif ($ob === 'lista')
                        <livewire:order-by-lista
                            :selected_console="$selected_console"
                            :game-sort-field="$gameSortField"
                            :game-sort-direction="$gameSortDirection"
                            :key="'lista-'.$gameSortField.'-'.$gameSortDirection.'-'.$selected_console->id"
                        />
                        @endif
                    </div>

                    <div class="leading-tight rounded-md overflow-hidden">
                        {{ $selected_console->description }}
                    </div>

                    <!-- Specs -->
                    <x-accordion
                        wire:click="toggleAccordion('specs_accordion')"
                        :toggler="$specs_accordion"
                    >
                        <x-slot name="title">Console Specifications</x-slot>
                        <div class="-mt-6">
                            @php $specs = $selected_console->specs ?? []; @endphp
                            <div class="w-full dark:text-cod-gray-100 leading-normal uppercase">CPU</div>
                            <div class="w-full leading-tight">{{ $specs['cpu'] ?? '' }}</div>
                            <div class="w-full dark:text-cod-gray-100 leading-normal capitalize mt-2">memory</div>
                            <div class="w-full leading-tight">{{ $specs['memory'] ?? '' }}</div>
                            <div class="w-full dark:text-cod-gray-100 leading-normal capitalize mt-2">graphics</div>
                            <div class="w-full leading-tight">{{ $specs['graphics'] ?? '' }}</div>
                            <div class="w-full dark:text-cod-gray-100 leading-normal capitalize mt-2">audio</div>
                            <div class="w-full leading-tight">{{ $specs['audio'] ?? '' }}</div>
                            <div class="w-full dark:text-cod-gray-100 leading-normal capitalize mt-2">input</div>
                            <div class="w-full leading-tight">{{ $specs['input'] ?? '' }}</div>
                        </div>
                    </x-accordion>

                    <!-- community links -->
                    <x-accordion
                        wire:click="toggleAccordion('community_accordion')"
                        :toggler="$community_accordion"
                    >
                        <x-slot name="title">Community links</x-slot>
                        <div class="-mt-6 grid grid-cols-1 xl:grid-cols-2">
                            @foreach ($selected_console->community_links ?? [] as $link)
                            <div class="py-3">
                                <a href="{{ $link['url'] }}" class="link flex items-center gap-x-2" target="_other_{{ $loop->index }}">
                                    <div class="w-5 h-5"><x-icons.link class="text-cod-gray-500" /></div>
                                    <div class="w-full leading-tight">{{ $link['community_name'] }}</div>
                                </a>
                            </div>
                            <div class="py-3 leading-tight">{{ $link['description'] }}</div>
                            @endforeach
                        </div>
                    </x-accordion>
                </div>

                <!-- right panel -->
                <div class="w-full xl:w-[30%] flex flex-col gap-y-4 items-start justify-start shadow-md shadow-cod-gray-400 dark:shadow-black border-[3.5px] border-cod-gray-200 dark:border-cod-gray-900 rounded-xl p-6 bg-cod-gray-100 dark:bg-cod-gray-900/60">
                    <div class="flex items-center justify-center w-full hover:scale-[1.4] xl:scale-[1.7] xl:hover:scale-[1.8] xl:hover:-translate-y-2 smooth-300">
                        @if($selected_console->console_icon)
                        <img class="my-6 w-full" src="{{ url($selected_console->console_icon) }}" alt="{{ $selected_console->short_name }}">
                        @endif
                    </div>

                    <!-- Available games -->
                    <div class="mt-8 flex flex-col w-full text-left gap-y-1">
                        <div class="flex items-center gap-x-2">
                            <x-icons.joystick class="w-[2.5rem] h-[2.5rem] opacity-50 text-cod-gray-700 dark:text-cod-gray-400" />
                            <span class="text-[2.5rem] text-cod-gray-900 dark:text-cod-gray-200">{{ $selected_console->games->count() }}</span>
                            {{ $selected_console->short_name === 'pc' ? 'games' : 'ROMs' }}
                        </div>
                    </div>

                    <x-accordion
                        wire:click="toggleAccordion('console_data_accordion')"
                        :toggler="$console_data_accordion"
                    >
                        <x-slot name="title">Console data</x-slot>
                        <div class="flex flex-col gap-y-6 items-start justify-start">
                            <div class="flex flex-col w-full text-left gap-y-1">
                                <div class="w-full dark:text-cod-gray-100 leading-none">Console name</div>
                                <div class="text-cod-gray-600 dark:w-full leading-none dark:text-cod-gray-400">
                                    {{ $selected_console->long_name }} ({{ $selected_console->short_name }})
                                </div>
                            </div>
                            <div class="flex flex-col w-full text-left gap-y-1">
                                <div class="w-full dark:text-cod-gray-100 leading-none">Manufacturer</div>
                                <div class="w-full leading-none text-cod-gray-600 dark:text-cod-gray-400">{{ $selected_console->manufacturer }}</div>
                            </div>
                            <div class="flex flex-col w-full text-left gap-y-1">
                                <div class="w-full dark:text-cod-gray-100 leading-none">Release year</div>
                                <div class="w-full leading-none text-cod-gray-600 dark:text-cod-gray-400">{{ $selected_console->release_year }}</div>
                            </div>
                            <div class="flex flex-col w-full text-left gap-y-1">
                                <div class="w-full dark:text-cod-gray-100 leading-none">Emulator</div>
                                <div class="w-full leading-none text-cod-gray-600 dark:text-cod-gray-400">{{ $selected_console->emulator_name }}</div>
                            </div>
                            <div class="flex flex-col w-full text-left gap-y-1">
                                <div class="w-full dark:text-cod-gray-100 leading-none">Emulator version</div>
                                <div class="w-full leading-none text-cod-gray-600 dark:text-cod-gray-400">{{ $selected_console->emulator_version }}</div>
                            </div>
                        </div>
                    </x-accordion>
                </div>
            </div>
        </div>
    </div>
</div>
