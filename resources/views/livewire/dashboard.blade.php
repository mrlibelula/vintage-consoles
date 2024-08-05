<div>
    <div class="{{ $show_hero ? 'relative h-[10rem] sm:h-[18rem] bg-cover bg-center bg-no-repeat' : '' }}"
        @if ($show_hero)
        style="filter: grayscale(1); background-image: url({{ $hero_image }});"
        @endif
    >
        
        @if ($show_hero)
        <div class="flex flex-col justify-between sm:-mt-16 h-full"
            :class="!darkMode ? 'fade-hero-light' : 'fade-hero'"
        >
            <div></div>
            <x-container class="mb-2 sm:mb-10 text-center md:text-left">
                
                {{-- <x-vintage-consoles class=" sm:text-3xl" /> --}}

                {{-- <div class="flex flex-col gap-y-2 md:gap-y-0 md:flex-row items-center gap-x-2 mt-4">
                    <x-danger-button class="w-[9.7rem] md:w-fit justify-center text-[0.88rem] md:text-[1.2rem] capitalize">
                        Browse games
                    </x-danger-button>
                    <x-secondary-button class="w-[9.7rem] md:w-fit justify-center text-[0.88rem] md:text-[1.2rem] capitalize border-0">
                        Hall of fame
                    </x-secondary-button>
                </div> --}}

            </x-container>
        </div>
        @endif
    </div>

    <x-container x-data="{ loader: false }" class="mt-6 sm:mt-10">
        <!-- console tabs -->
        <div 
            @loader-on.window="loader = true" 
            @loader-off.window="loader = false" 
            class="grid grid-cols-5 gap-x-2"
        >
            @foreach ($consoles as $console)
            <a 
                {{-- @click="loader = true"  --}}
                @click="$dispatch('loader-top-on')" 
                wire:navigate href="{{ '/' . $console['short_name'] }}"
                class="z-40 group flex cursor-pointer {{ $selected_console_id !== $console['id'] ? '' : 'bg-cod-gray-600/20 rounded-t-lg md:rounded-t-xl' }} px-4 py-2 xl:py-6 items-center justify-center w-full text-xl md:text-2xl overflow-hidden"
            >
                <div class="flex flex-col gap-y-4 sm:gap-y-0 justify-between items-center text-center h-full w-full">
                    <img class="hidden xl:block h-[2.8rem] {{ $selected_console_id !== $console['id'] ? 'grayscale' : '' }} group-hover:grayscale-0 smooth-500" src="{{ $console['console_logo'] }}" alt="{{ $console['short_name'] }}">
                    <div class="block xl:hidden whitespace-nowrap text-base md:text-xl {{ $selected_console_id !== $console['id'] ? 'text-gray-500 dark:text-cod-gray-600' : 'text-cod-gray-900 dark:text-cod-gray-200' }}">
                        @if (strtoupper($console['short_name']) === 'ATARI2600')
                        ATARI
                        @else
                        {{ strtoupper($console['short_name']) }}
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        @if ($selected_console)
        <!-- loader -->
        <template x-cloak x-if="loader">
            <div class="flex items-center justify-center">
                <span class="loader-71 absolute h-0"></span>
            </div>
        </template>
        <livewire:selected-console 
            :is_selected_tab_first="$this->isSelectedTabFirst()" 
            :is_selected_tab_last="$this->isSelectedTabLast()" 
            :selected_console="$selected_console"
            :key="$selected_console['id']" 
        />
        @endif
    </x-container>
</div>