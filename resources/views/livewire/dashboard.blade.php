<div>
    <div class="{{ $show_hero ? 'relative h-[10rem] sm:h-[18rem] bg-cover bg-center bg-no-repeat' : '' }}"
        @if ($show_hero)
        style="filter: grayscale(1); background-image: url({{ $hero_image }}"
        @endif
    >
        
        @if ($show_hero)
        <div class="fade-hero flex flex-col justify-between h-full">
            <div></div>
            <x-container class="mb-2 sm:mb-10 text-center md:text-left">
                
                <x-vintage-consoles />
                
                {{-- <div class="sm:text-[1.7rem] text-cod-gray-900 dark:text-cod-gray-400 leading-none">
                    Online Emulators
                </div> --}}

                <div class="flex flex-col gap-y-2 md:gap-y-0 md:flex-row items-center gap-x-2 mt-4">
                    <x-danger-button class="w-[9.5rem] md:w-fit justify-center text-[0.88rem] md:text-[1.2rem] capitalize">
                        browse all games
                    </x-danger-button>
                    <x-secondary-button class="w-[9.5rem] md:w-fit justify-center text-[0.88rem] md:text-[1.2rem] capitalize border-0">
                        Hall of fame →
                    </x-secondary-button>
                </div>

            </x-container>
        </div>
        @endif
    </div>

    <x-container class="mt-6 sm:mt-10">
        <!-- consoles -->
        <div class="grid grid-cols-5 gap-x-2">
            @foreach ($consoles as $console)
            <div wire:click.prevent="setConsole({{ $console['id'] }})" class="group flex cursor-pointer sepia_ {{ $selected_console_id !== $console['id'] ? '' : 'bg-cod-gray-800/50 rounded-t-xl' }} px-4 py-2 xl:py-6 items-center justify-center w-full text-xl md:text-2xl overflow-hidden">
                <div class="flex flex-col gap-y-4 sm:gap-y-0 justify-between items-center text-center h-full w-full">
                    <img class="hidden xl:block h-[2.8rem] {{ $selected_console_id !== $console['id'] ? 'grayscale' : '' }} group-hover:grayscale-0 transition duration-500 ease-in-out" src="{{ $console['console_logo'] }}" alt="{{ $console['short_name'] }}">
                    <div class="block xl:hidden whitespace-nowrap text-base md:text-xl {{ $selected_console_id !== $console['id'] ? 'text-cod-gray-600' : 'text-cod-gray-200' }}">{{ strtoupper($console['short_name']) }}</div>
                </div>
            </div>
            @endforeach
        </div>

        @if ($selected_console)
        <livewire:selected-console 
            :is_selected_tab_first="$this->isSelectedTabFirst()" 
            :is_selected_tab_last="$this->isSelectedTabLast()" 
            :selected_console="$selected_console"
            :key="$selected_console['id']" 
        />
        @endif
    </x-container>
</div>