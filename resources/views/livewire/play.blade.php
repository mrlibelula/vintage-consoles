<x-container class="h-screen -mt-[4rem]">
    <div class="flex flex-col gap-y-8">
        <!-- player & user data -->
        <div class="flex flex-col gap-x-0 gap-y-1 xl:gap-x-1 xl:gap-y-0 xl:flex-row sticky top-[4rem] items-start justify-between rounded-md overflow-hidden border-[4px] xl:border-0 border-cod-gray-800/50 shadow-xl shadow-black">
            <div class="w-full xl:w-[60%]">
                <!-- pc dosbox player -->
                @if (strtolower($console['short_name']) === 'pc')
                <iframe class="game-arena" frameborder="0"
                    src="https://dos.zone/player/?bundleUrl={{ $game['rom'] }}&anonymous=1"
                    allowfullscreen>
                </iframe>
                @else
                {{-- 'title', 'short_name', 'game_url', 'game_id' --}}
                <iframe class="game-arena" frameborder="0"
                    src="{{ $player_route }}"
                    allowfullscreen>
                </iframe>
                @endif
            </div>

            <!-- user data -->
            <div class="xl:w-[40%] xl:flex xl:flex-col w-full">
                <!-- tabs -->
                <div class="flex items-center justify-between gap-x-1">
                    <x-tab-item :active="true">Info</x-tab-item>
                    <x-tab-item :active="false">State</x-tab-item>
                    <x-tab-item :active="false">Multiplayer</x-tab-item>
                    <x-tab-item :active="false">
                        <div class="flex items-center justify-center text-base md:text-xl">
                            <x-red-dot />
                            Live chat
                        </div>
                    </x-tab-item>
                </div>
                
                <!-- tab main content -->
                <x-tab-content class="flex flex-col gap-y-4">
                    <div class="flex flex-row-reverse items-start justify-center gap-x-8">
                        @if ($game['cartridge'])
                        <div class="w-[38%] bg-black flex items-center justify-center overflow-hidden rounded-md shadow-md shadow-black">
                            <img class=" w-full " src="{{ $game['cartridge'] }}" alt="">
                        </div>
                        @else
                        <div class="w-[38%] bg-black flex items-center justify-center overflow-hidden rounded-md shadow-md shadow-black">
                            <img class=" w-full " src="{{ $game['poster'] }}" alt="">
                        </div>
                        @endif
                        <div class="@if ($game['cartridge']) w-[62%] @else w-full @endif">
                            <div class="flex flex-col">
                                <div class="mb-2">
                                    <img class=" w-[45%]" src="{{ $console['console_logo'] }}" alt="">
                                </div>
                                <div class=" leading-tight text-2xl text-cod-gray-50">
                                    {{ $game['title'] }}
                                </div>
                                <div class=" leading-tight text-cod-gray-400">
                                    {{ $game['publisher'] }}
                                </div>
                                <div class=" leading-tight text-cod-gray-400">
                                    {{ $game['release_year'] }}
                                </div>
                                <div class=" leading-tight text-cod-gray-400">
                                    <div class="flex items-center gap-x-1">
                                        <div>
                                            <x-icons.star class="w-4 h-4" fill="#a7a7a7" />
                                        </div>
                                        {{ number_format($game['rating'] * 100, 0) }}%

                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- <div class="w-1/2 bg-black p-1.5 flex items-center justify-center">
                            <img class=" w-full " src="{{ $game['poster'] }}" alt="">
                        </div> --}}
                    </div>
                    <!-- game info -->
                    <div class="flex flex-col-reverse gap-y-6 lg:gap-y-0 lg:flex-row items-center lg:items-start justify-between gap-x-4">
                        <div class="w-full grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-2 gap-6">
                            {{-- <x-desc-list title="Game title">{{ $game['title'] }}</x-desc-list>
                            <x-desc-list title="Publisher">{{ $game['publisher'] }}</x-desc-list>
                            <x-desc-list title="Rating">{{ number_format($game['rating'] * 100, 0) }}%</x-desc-list>
                            <x-desc-list title="Release year">{{ $game['release_year'] }}</x-desc-list> --}}
                            
                            {{-- <x-desc-list title="Multiplayer">
                                @if ($game['multiplayer_support'])
                                <x-icons.check class=" text-green-500 w-6 h-6" />
                                @else
                                <x-icons.x class=" text-rose-500 w-6 h-6" />
                                @endif
                            </x-desc-list>
                            <x-desc-list title="Save state">
                                @if ($game['save_state_support'])
                                <x-icons.check class=" text-green-500 w-6 h-6" />
                                @else
                                <x-icons.x class=" text-rose-500 w-6 h-6" />
                                @endif
                            </x-desc-list> --}}
                        </div>

                        <!-- poster -->
                        {{-- <img class="w-[7rem] md:w-[8rem] border-[4px] border-cod-gray-500/50 shadow-md shadow-black brightness-75 rounded-md" src="{{ $game['poster'] }}" alt="{{ $game['title'] }}"> --}}
                    </div>
                    
                    <!-- console -->
                    {{-- <x-accordion wire:click="toggle('console')" :toggler="$accordion_toggler['console']">
                        <x-slot name="title">Console</x-slot>
                        <div class=" w-1/2">
                            <img src="{{ $console['console_logo'] }}" alt="{{ $console['short_name'] }}">
                        </div>
                    </x-accordion> --}}
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
                    
                    <x-accordion wire:click="toggle('screenshots')" :toggler="$accordion_toggler['screenshots']">
                        <x-slot name="title">Screenshots</x-slot>
                        screenshots here
                    </x-accordion>

                </x-tab-content>
            </div>
        </div>

    </div>

    @if (strtolower($console['short_name']) !== 'pc')
        @push('scripts')
        
        @endpush
    @endif

</x-container>
