<div class="flex flex-col gap-y-6 w-full bg-cod-gray-600/20 p-0 lg:p-2  
    {{ $is_selected_tab_first ? 'rounded-b-xl rounded-tr-xl' : ($is_selected_tab_last ? 'rounded-b-xl rounded-tl-xl' : 'rounded-xl') }} 
">
    <div class="flex flex-col-reverse xl:flex-row items-start justify-between gap-x-10 w-full">
        <!-- main -->
        <div class="w-full flex flex-col gap-y-8 items-start justify-start p-4">

            <div class="w-full flex flex-col xl:flex-row gap-y-8 gap-x-[4rem] items-start justify-start">
                
                <!-- main content -->
                <div 
                    x-data="{ 
                        skeletonSquare: false,
                        skeletonGroup: false,
                        skeletonLista: false,
                    }" 
                    
                    @skeleton-square-off.window="skeletonSquare = false"
                    @skeleton-square-on.window="skeletonSquare = true"

                    @skeleton-group-off.window="skeletonGroup = false"
                    @skeleton-group-on.window="skeletonGroup = true"

                    @skeleton-lista-off.window="skeletonLista = false"
                    @skeleton-lista-on.window="skeletonLista = true"

                    class="flex flex-col gap-y-8 w-full xl:w-[70%] main-container">
                    
                    <!-- game list display options -->
                    <div class="flex items-center justify-start gap-x-3 w-full dark:text-cod-gray-500 leading-none -mb-8">
                        <a @click="$dispatch('loader-top-on'); $dispatch('skeleton-group-on');"  wire:navigate href="/{{ $selected_console['short_name'] }}?ob=group" class="btn-small"><x-icons.group class="{{ $ob === 'group' ? ' text-rose-700 dark:text-gray-200' : '' }}" /></a>
                        <a @click="$dispatch('loader-top-on'); $dispatch('skeleton-square-on');"  wire:navigate href="/{{ $selected_console['short_name'] }}?ob=squares" class="btn-small"><x-icons.squares class="{{ $ob === 'squares' ? ' text-rose-700 dark:text-gray-200' : '' }}" /></a>
                        <a @click="$dispatch('loader-top-on'); $dispatch('skeleton-lista-on');"  wire:navigate href="/{{ $selected_console['short_name'] }}?ob=lista" class="btn-small"><x-icons.lista class="{{ $ob === 'lista' ? ' text-rose-700 dark:text-gray-200' : '' }}" /></a>
                    </div>

                    <!-- skeleton group -->
                    <template x-if="skeletonGroup">
                        <x-ribbon>
                            <x-skeleton-group :count="20" />
                        </x-ribbon>
                    </template>
                    
                    <!-- skeleton square -->
                    <template x-if="skeletonSquare">                        
                        <x-ribbon>
                            <x-skeleton-square :count="20" />
                        </x-ribbon>
                    </template>
                    
                    <!-- skeleton lista -->
                    <template x-if="skeletonLista">
                        <x-skeleton-lista  />
                    </template>

                    <div :class="{ 'hidden': skeletonGroup || skeletonSquare || skeletonLista }">
                        @if ($ob === 'group')
                        <livewire:order-by-group :selected_console="$selected_console" :key="uniqid()" />
                        @elseif ($ob === 'squares')
                        <livewire:order-by-squares :selected_console="$selected_console" :key="uniqid()" />
                        @elseif ($ob === 'lista')
                        <livewire:order-by-lista :selected_console="$selected_console" :key="uniqid()" />
                        @endif
                    </div>


                    <div class="leading-tight rounded-md overflow-hidden">
                        {{ $selected_console['description'] }}
                    </div>  

                    <!-- Specs -->
                    <x-accordion  
                        wire:click="toggleAccordion('specs_accordion')"
                        :toggler="$specs_accordion"
                    >
                        <x-slot name="title">Specifications</x-slot>
                        <div class="-mt-6">
                            <div class="w-full dark:text-cod-gray-100 leading-normal uppercase">
                                CPU
                            </div>
                            <div class="w-full leading-tight ">
                                {{ $selected_console['specs']['cpu'] ?? '' }}
                            </div>
                        
                            <div class="w-full dark:text-cod-gray-100 leading-normal capitalize  mt-2">
                                memory
                            </div>
                            <div class="w-full leading-tight ">
                                {{ $selected_console['specs']['memory'] ?? '' }}
                            </div>
                        
                            <div class="w-full dark:text-cod-gray-100 leading-normal capitalize  mt-2">
                                graphics
                            </div>
                            <div class="w-full leading-tight ">
                                {{ $selected_console['specs']['graphics'] ?? '' }}
                            </div>
                        
                            <div class="w-full dark:text-cod-gray-100 leading-normal capitalize  mt-2">
                                audio
                            </div>
                            <div class="w-full leading-tight ">
                                {{ $selected_console['specs']['audio'] ?? '' }}
                            </div>
                        
                            <div class="w-full dark:text-cod-gray-100 leading-normal capitalize  mt-2">
                                input
                            </div>
                            <div class="w-full leading-tight ">
                                {{ $selected_console['specs']['input'] ?? '' }}
                            </div>
                        </div>
                    </x-accordion>

                    <!-- community links -->
                    <x-accordion
                        wire:click="toggleAccordion('community_accordion')"
                        :toggler="$community_accordion"
                    >
                        <x-slot name="title">Community links</x-slot>
                        <div class="-mt-6 grid grid-cols-1 xl:grid-cols-2">
                            @foreach ($selected_console['community_links'] as $link)
                            <div class="py-3">
                                <a href="{{ $link['url'] }}" class="link flex items-center gap-x-2" target="_other_{{ $loop->index }}" class="py-3">
                                    <div class="w-5 h-5">
                                        <x-icons.link class="text-cod-gray-500" />
                                    </div>
                                    <div class=" w-full leading-tight">
                                        {{ $link['community_name'] }}
                                    </div>
                                </a>
                            </div>
                            <div class="py-3 leading-tight">
                                {{ $link['description'] }}
                            </div>
                            @endforeach
                        </div>
                    </x-accordion>
                </div>
                
                <!-- right panel -->
                <div class="w-full xl:w-[30%] flex flex-col gap-y-4 items-start justify-start shadow-md shadow-cod-gray-400 dark:shadow-black border border-cod-gray-400 dark:border-cod-gray-900 bg-gradient-to-tr from-cod-gray-400/20 via-cod-gray-300/50 to-cod-gray-100 dark:from-cod-gray-800 dark:via-cod-gray-700/30 dark:to-cod-gray-600/80 rounded-md p-5">
                    <div class="flex items-center justify-center w-full xl:scale-[1.35] xl:hover:scale-[1.45] xl:hover:-translate-y-2 smooth-300">
                        <img class="my-6 w-full" src="{{ $selected_console['console_icon'] }}" alt="{{ $selected_console['short_name'] }}">
                    </div>

                    <x-accordion
                        class="mt-8"
                        wire:click="toggleAccordion('console_data_accordion')"
                        :toggler="$console_data_accordion"
                    >
                        <x-slot name="title">Console data</x-slot>
                        <div class="flex flex-col gap-y-6 items-start justify-start">
                            <!-- Console -->
                            <div class="flex flex-col w-full text-left gap-y-1">
                                <div class=" w-full dark:text-cod-gray-100 leading-none">
                                    Console name
                                </div>
                                <div class=" text-cod-gray-600 dark:w-full leading-none dark:text-cod-gray-400">
                                    {{ $selected_console['long_name'] }} ({{ $selected_console['short_name'] }})
                                </div>
                            </div>
            
                            <!-- Manufacturer -->
                            <div class="flex flex-col w-full text-left gap-y-1">
                                <div class=" w-full dark:text-cod-gray-100 leading-none">
                                    Manufacturer
                                </div>
                                <div class=" w-full leading-none text-cod-gray-600 dark:text-cod-gray-400">
                                    {{ $selected_console['manufacturer'] }}
                                </div>
                            </div>
                            
                            <!-- Release year -->
                            <div class="flex flex-col w-full text-left gap-y-1">
                                <div class=" w-full dark:text-cod-gray-100 leading-none">
                                    Release year
                                </div>
                                <div class=" w-full leading-none text-cod-gray-600 dark:text-cod-gray-400">
                                    {{ $selected_console['release_year'] }}
                                </div>
                            </div>

                            <!-- Available games -->
                            <div class="flex flex-col w-full text-left gap-y-1">
                                <div class=" w-full dark:text-cod-gray-100 leading-none">
                                    Available games
                                </div>
                                <div class=" w-full leading-none text-cod-gray-600 dark:text-cod-gray-400">
                                    Found {{ count($selected_console['games']) }} ROMs
                                </div>
                            </div>

                            <!-- Emulator info -->
                            <div class="flex flex-col w-full text-left gap-y-1">
                                <div class=" w-full dark:text-cod-gray-100 leading-none">
                                    Emulator
                                </div>
                                <div class=" w-full leading-none text-cod-gray-600 dark:text-cod-gray-400">
                                    {{ $selected_console['emulator']['name'] }}
                                </div>
                            </div>

                            <!-- Emulator version -->
                            <div class="flex flex-col w-full text-left gap-y-1">
                                <div class=" w-full dark:text-cod-gray-100 leading-none">
                                    Emulator version
                                </div>
                                <div class=" w-full leading-none text-cod-gray-600 dark:text-cod-gray-400">
                                    {{ $selected_console['emulator']['version'] }}
                                </div>
                            </div>
            
                        </div>
                    </x-accordion>

                </div>
            </div>


        </div>

    </div>
</div>