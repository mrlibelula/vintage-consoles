<div x-data="{ isOpen: true }">
    <!-- ribbon title -->
    <div x-on:click="isOpen = ! isOpen" class="flex items-center px-6 justify-start py-2 md:py-6 text-gray-400 cursor-pointer">
        <div class="flex items-center text-xl xl:text-2xl text-gray-400">

            {{-- <x-icon-stream class="animate-pulse text-gray-400 w-6 h-6 xl:w-7 xl:h-7 mt-1 mr-2" /> --}}

            <div class="flex">
                {{ __('Available games') }}
                {{-- <div class="font-libeflix md:mt-0.5 tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-gray-500 via-gray-800 to-gray-500 hover:from-gray-400 hover:via-gray-600 hover:to-gray-400 transition duration-150 ease-in-out">
                    &nbsp;LibefliX&nbsp;
                </div> --}}
                ({{ count($games) }})
            </div>
        </div>
        <div class="ml-2">
            <i x-cloak x-show="isOpen" class="text-rose-500 caret down icon"></i>
            <i x-cloak x-show="!isOpen" class="text-rose-500 caret right icon"></i>
        </div>
    </div>

    <!-- responsive ribbon: now streaming -->
    <div x-cloak x-show.transition.duration.500ms.opacity="isOpen"
        class="overflow-x-auto" 
        
        {{-- style="
            scrollbar-width: thin;
            scrollbar-color: #252f3f #13161c ;
        " --}}
        >

            {{-- @livewire('sort-by-ribbon', [
                'toggles' => $toggles_streaming,
                'toggles_prop_name' => 'toggles_streaming',
                'recieved_results' => $games,
                'prop_name' => 'games',
            ], key(uniqid())) --}}

            @livewire('ribbon', [
                'data' => $games,
                'template' => 'game-card',
                'template_var_name' => 'game',
            ], key(uniqid()))
    </div>
</div>