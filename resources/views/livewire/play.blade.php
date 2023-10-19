<x-container class="h-screen -mt-[4rem]">
    <div class="flex flex-col gap-y-8">
        <!-- player & user data -->
        <div class="flex flex-col gap-x-0 gap-y-1 xl:gap-x-2 xl:gap-y-0 xl:flex-row sticky items-start justify-between overflow-hidden">
            <!-- player -->
            <div class="w-full xl:w-[70%] bg-black h-full rounded-lg overflow-hidden">
                <iframe class="game-arena" frameborder="0"
                    @if (strtolower($console['short_name']) === 'pc')
                    src="https://dos.zone/player/?bundleUrl={{ $game['rom'] }}&anonymous=1"
                    @else
                    src="{{ $player_route }}"
                    @endif
                    allowfullscreen>
                </iframe>
            </div>

            <!-- game panel -->
            <div class="xl:w-[30%] xl:flex xl:flex-col w-full mb-4">
                
                <!-- tabs -->
                <div class="flex items-center justify-between gap-x-1">
                    <x-tab-item wire:click="changeTab('info')" @click="$dispatch('loader-top-on')" :active="$tabs['info']">
                        Game info
                    </x-tab-item>
                    
                    <x-tab-item wire:click="changeTab('chat')" @click="$dispatch('loader-top-on')" :active="$tabs['chat']">
                        <div class="flex items-center justify-center text-base md:text-xl">
                            <x-red-dot /> Live chat
                        </div>
                    </x-tab-item>
                </div>
                
                <!-- info tab -->
                <x-tab-content>
                    @if ($tabs['info'])
                    <div class="flex flex-col gap-y-4 p-4">
                        <!-- header -->
                        <div class="flex flex-row-reverse items-start justify-center gap-x-4">
                            <!-- console & game images -->
                            @if ($game['cartridge'])
                            <div class="w-[38%] bg-black flex flex-col-reverse items-center justify-center overflow-hidden rounded-md shadow-md shadow-black">
                                <img class="w-full" src="{{ $game['cartridge'] }}" alt="{{ $game['title'] }}">
                                <div class="my-2 flex justify-center">
                                    <img class=" w-[75%]" src="{{ $console['console_logo'] }}" alt="{{ $console['short_name'] }}">
                                </div>
                            </div>
                            @else
                            <div class="w-[38%] bg-black flex flex-col-reverse items-center justify-center overflow-hidden rounded-md shadow-md shadow-black">
                                <img class=" w-full " src="{{ $game['poster'] }}" alt="">
                                <div class="my-2 flex justify-center">
                                    <img class=" w-[75%]" src="{{ $console['console_logo'] }}" alt="{{ $console['short_name'] }}">
                                </div>
                            </div>
                            @endif
                            
                            <!-- game info -->
                            <div class="@if ($game['cartridge']) w-[62%] @else w-full @endif">
                                <div class="flex flex-col gap-y-1">
                                    <div class=" leading-none text-2xl text-cod-gray-50">
                                        {{ $game['title'] }}
                                    </div>
                                    <div class=" leading-none text-cod-gray-400">
                                        {{ $game['publisher'] }}
                                    </div>
                                    <div class="flex items-center gap-x-1 justify-start">
                                        <div class=" leading-none text-cod-gray-200">
                                            {{ $game['release_year'] }}
                                        </div>
                                        <div class=" text-cod-gray-500">
                                            •
                                        </div>
                                        <div class=" leading-none text-cod-gray-200">
                                            {{ number_format($game['rating'] * 100, 0) }}%
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-x-1 justify-start">
                                        <div class=" leading-none text-cod-gray-400">
                                            State:
                                        </div>
                                        @if ($game['save_state_support'])
                                        <div class=" leading-none text-green-500">
                                            Yes
                                        </div>
                                        @else
                                        <div class=" leading-none text-rose-500">
                                            No
                                        </div>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-x-1 justify-start">
                                        <div class=" leading-none text-cod-gray-400">
                                            Multiplayer:
                                        </div>
                                        @if ($game['multiplayer_support'])
                                        <div class=" leading-none text-green-500">
                                            Yes
                                        </div>
                                        @else
                                        <div class=" leading-none text-rose-500">
                                            No
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        
                        <!-- screenshots ribbon -->
                        <x-accordion wire:click="toggle('screenshots')" :toggler="$accordion_toggler['screenshots']">
                            <x-slot name="title">Screenshots</x-slot>
                            <div class="flex pb-6 overflow-hidden overflow-x-auto flex-col">
                                <div class="flex flex-no-wrap gap-x-2">
                                    @forelse ($game['screenshots'] as $key => $img)
                                    <img @click="$dispatch('open-fixed-modal'); $dispatch('fixed-modal-loader-on')" wire:click="screenshot({{ $key }})" src="{{ $img }}" alt="{{ $key }}" class="rounded-md w-[10rem] h-[6rem] brightness-75 hover:brightness-100 smooth-300 cursor-pointer">
                                    @empty
                                    <div class="flex flex-col cursor-default rounded-xl py-6 w-full text-cod-gray-300">
                                        <div class="text-center text-3xl">
                                            ¯\_(ツ)_/¯
                                        </div>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </x-accordion>
    
                        <!-- description -->
                        <x-accordion wire:click="toggle('description')" :toggler="$accordion_toggler['description']">
                            <x-slot name="title">Description</x-slot>
                            {{ $game['description'] }}
                        </x-accordion>
    
                        <!-- genres -->
                        <x-accordion wire:click="toggle('genres')" :toggler="$accordion_toggler['genres']">
                            <x-slot name="title">Genres</x-slot>
                            <div class="flex flex-col gap-y-4">
                                @foreach ($game['genres'] as $genre)
                                <x-tag>#{{ $genre['name'] }}</x-tag>
                                {{ $genre['description'] }}
                                @endforeach
                            </div>
                        </x-accordion>
                    </div>
                    @endif

                    @if ($tabs['chat'])
                    <div class="h-[90%]">
                        @livewire('chat', [
                            'console_id' => $console['id'], 
                            'game' => $game, 
                        ], uniqid())
                        <!-- chat input -->
                        <div class="h-[10%] flex items-center justify-center py-1 rounded-b-md bg-cod-gray-950">
                            <div class="w-full px-0.5">
                                <x-input wire:model.live.lazy="input" placeholder="New message here..." class="h-[1.7rem] w-full border-none dark:placeholder-cod-gray-600/70 dark:bg-cod-gray-600/25 focus:ring-2 rounded-b-md rounded-t-none text-base" />
                            </div>
                        </div>
                    </div>
                    @endif
                </x-tab-content>

            </div>
        </div>

        <!-- screenshots modal -->
        <x-fixed-modal :width="80" :height="80">
            @if ($current_screenshot_key !== -1)
            <x-slot name="title">
                <div class=" w-full">
                    <div class="w-full text-center">
                        <div class="flex flex-col">
                            <div class=" text-2xl text-cod-gray-100">
                                {{ $game['title'] }}
                            </div>
                        </div>
                        
                    </div>
                    <div class="flex text-center w-full items-center justify-center gap-x-2 text-cod-gray-500">
                        <div class=" leading-none">
                            Screenshot
                        </div>
                        <div class="leading-none">
                            {{ $current_screenshot_key + 1 }}/{{ count($game['screenshots']) }}
                        </div>
                    </div>
                </div>
            </x-slot>
            
            <div class="px-6">
                <div class="flex items-center justify-between gap-x-[0.16rem] md:gap-x-2 xl:gap-x-4">
                    <div @click="$dispatch('fixed-modal-loader-on')" wire:click="changeScreenShot('left')" class="p-1">
                        <x-icons.arrow-left class="w-3 md:w-5 xl:w-7 text-cod-gray-200 hover:text-rose-500 smooth-300 cursor-pointer" />
                    </div>
                    <div class="w-[100vw] h-[15vh] sm:h-[23vh] md:h-[40vh] xl:h-[60vh]">
                        <img class="mb-4 w-full h-full rounded-md" src="{{ $game['screenshots'][$current_screenshot_key] }}" alt="Screenshot: {{ $game['title'] }}">
                    </div>
                    <div @click="$dispatch('fixed-modal-loader-on')" wire:click="changeScreenShot('right')" class="p-1">
                        <x-icons.arrow-right class="w-3 md:w-5 xl:w-7 text-cod-gray-200 hover:text-rose-500 smooth-300 cursor-pointer" />
                    </div>
                </div>
            </div>
            @endif
        </x-fixed-modal>

    </div>
</x-container>
