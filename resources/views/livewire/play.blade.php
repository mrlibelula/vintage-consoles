<x-container class="h-screen -mt-[4rem]">
    <div class="flex flex-col gap-y-8">
        <!-- player & user data -->
        <div class="flex flex-col gap-x-0 gap-y-1 xl:gap-x-1 xl:gap-y-0 xl:flex-row sticky top-[4rem] items-start justify-between rounded-md overflow-hidden border-[4px] xl:border-0 border-cod-gray-800/50 shadow-xl shadow-black">
            <div class="w-full xl:w-[60%]">
                <iframe class="w-full h-[30vh] sm:h-[35vh] md:h-[36vh] lg:h-[37vh] xl:h-[50vh] border-0 m-0 p-0 rounded-md_ overflow-hidden" frameborder="0"
                    {{-- src="{{ $full_path }}" --}}
                    {{-- src="https://dos.zone/player/?bundleUrl=https%3A%2F%2Flibe.dev%2Fbundles%2Fprince.jsdos&anonymous=1" --}}
                    allowfullscreen>
                </iframe>
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
                    <div class="flex flex-col-reverse gap-y-6 lg:gap-y-0 lg:flex-row items-center lg:items-start justify-between gap-x-4">

                        <div class="w-full grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-2 gap-6">
                            <x-desc-list title="Game title">{{ $game['title'] }}</x-desc-list>
                            <x-desc-list title="Publisher">{{ $game['publisher'] }}</x-desc-list>
                            <x-desc-list title="Rating">{{ number_format($game['rating'] * 100, 0) }}%</x-desc-list>
                            <x-desc-list title="Release year">{{ $game['release_year'] }}</x-desc-list>
                        </div>

                        <!-- poster -->
                        {{-- <img class="w-[7rem] md:w-[8rem] border-[4px] border-cod-gray-500/50 shadow-md shadow-black brightness-75 rounded-md" src="{{ $game['poster'] }}" alt="{{ $game['title'] }}"> --}}
                    </div>

                    <x-accordion wire:click="toggle('description')" :toggler="$accordion_toggler['description']">
                        <x-slot name="title">Description</x-slot>
                        {{ $game['description'] }}
                    </x-accordion>

                    <x-accordion wire:click="toggle('genres')" :toggler="$accordion_toggler['genres']">
                        <x-slot name="title">Genres</x-slot>
                        Genres
                    </x-accordion>
                    
                    <x-accordion wire:click="toggle('screenshots')" :toggler="$accordion_toggler['screenshots']">
                        <x-slot name="title">Screenshots</x-slot>
                        screenshots here
                    </x-accordion>

                </x-tab-content>
            </div>
        </div>

    </div>

</x-container>
