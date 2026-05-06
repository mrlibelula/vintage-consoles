<x-container class="h-screen -mt-[4rem]">
    <div class="flex flex-col gap-y-8">
        <!-- player & user data -->
        <div class="flex flex-col gap-x-0 gap-y-1 xl:gap-x-2 xl:gap-y-0 xl:flex-row sticky items-start justify-between overflow-hidden">
            <!-- player -->
            <div id="game-container" class="w-full xl:w-[70%] bg-black h-full rounded-lg overflow-hidden relative">
                <iframe id="game-iframe" class="game-arena" frameborder="0" scrolling="no"
                    @if (strtolower($console['short_name']) === 'pc')
                    {{-- src="https://dos.zone/player/?bundleUrl={{ $game['rom'] }}&anonymous=1" --}}
                    src="{{ route('dosplayer', [
                        \App\Service\Tool::encode(json_encode($game)),
                        strtolower($console['short_name']),
                    ]) }}"
                    onload="hideDosIframeLoader()"
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
                            <a wire:navigate href="/{{ $console['short_name'] }}" class="w-[38%] bg-black flex flex-col-reverse items-center justify-center overflow-hidden rounded-md shadow-md shadow-black">
                                <img class="w-full" src="{{ $game['cartridge'] }}" alt="{{ $game['title'] }}">
                                <div class="my-2 flex justify-center">
                                    <img class=" w-[75%]" src="{{ $console['console_logo'] }}" alt="{{ $console['short_name'] }}">
                                </div>
                            </a>
                            @else
                            <a wire:navigate href="/{{ $console['short_name'] }}" class="w-[38%] bg-black flex flex-col-reverse items-center justify-center overflow-hidden rounded-md shadow-md shadow-black">
                                <img class=" w-full " src="{{ $game['poster'] }}" alt="">
                                <div class="my-2 flex justify-center">
                                    <img class=" w-[75%]" src="{{ $console['console_logo'] }}" alt="{{ $console['short_name'] }}">
                                </div>
                            </a>
                            @endif
                            
                            <!-- game info -->
                            <div class="@if ($game['cartridge']) w-[62%] @else w-full @endif">
                                <div class="flex flex-col gap-y-1">
                                    <div class=" leading-none text-2xl text-black dark:text-cod-gray-50">
                                        {{ $game['title'] }}
                                    </div>
                                    <div class=" leading-none text-cod-gray-900 dark:text-cod-gray-400">
                                        {{ $game['publisher'] }}
                                    </div>
                                    <div class="flex items-center gap-x-1 justify-start">
                                        <div class=" leading-none text-cod-gray-900 dark:text-cod-gray-200">
                                            {{ $game['release_year'] }}
                                        </div>
                                        <div class=" text-cod-gray-900 dark:text-cod-gray-500">
                                            •
                                        </div>
                                        <div class=" leading-none text-cod-gray-900 dark:text-cod-gray-200">
                                            {{ number_format($game['rating'] * 100, 0) }}%
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-x-1 justify-start">
                                        <div class=" leading-none text-cod-gray-900 dark:text-cod-gray-400">
                                            State:
                                        </div>
                                        @if ($game['save_state_support'])
                                        <div class=" leading-none text-green-700 dark:text-green-500">
                                            Yes
                                        </div>
                                        @else
                                        <div class=" leading-none text-rose-700 dark:text-rose-500">
                                            No
                                        </div>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-x-1 justify-start">
                                        <div class=" leading-none text-cod-gray-900 dark:text-cod-gray-400">
                                            Multiplayer:
                                        </div>
                                        @if ($game['multiplayer_support'])
                                        <div class=" leading-none text-green-700 dark:text-green-500">
                                            Yes
                                        </div>
                                        @else
                                        <div class=" leading-none text-rose-700 dark:text-rose-500">
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
                                <a href="{{ route('genres', $genre['name']) }}" target="_genres_{{ $genre['name'] }}_{{ $loop->index }}">
                                    <x-tag class="text-left">#{{ $genre['name'] }}</x-tag>
                                </a>
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
                                <x-input wire:model.live.lazy="input" placeholder="New message here..." class="h-[1.7rem] w-full border-none dark:placeholder-cod-gray-600/70 bg-cod-gray-300 dark:bg-cod-gray-600/25 focus:ring-2 rounded-b-md rounded-t-none text-base" />
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

<!-- DOS Iframe Loader JavaScript -->
@if (strtolower($console['short_name']) === 'pc')
<script>
    // Use sessionStorage to persist state across Livewire updates
    const STORAGE_KEY = 'dos_loader_{{ $game["id"] ?? "default" }}_hidden';
    const GAME_TITLE = @json($game['title'] ?? 'DOS Game');
    
    function isDosLoaderHidden() {
        return sessionStorage.getItem(STORAGE_KEY) === 'true';
    }
    
    function markDosLoaderAsHidden() {
        sessionStorage.setItem(STORAGE_KEY, 'true');
    }

    function createDosLoader() {
        // Don't create if already hidden
        if (isDosLoaderHidden()) {
            return;
        }

        // Check if loader already exists
        if (document.getElementById('dynamic-dos-loader')) {
            return;
        }

        const container = document.getElementById('game-container');
        if (!container) return;

        const loader = document.createElement('div');
        loader.id = 'dynamic-dos-loader';
        loader.innerHTML = `
            <div style="
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: #000000;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                z-index: 50;
                color: white;
                font-family: system-ui, -apple-system, sans-serif;
                padding: 16px;
            ">
                <div class="dos-spinner" style="
                    width: 48px;
                    height: 48px;
                    border: 4px solid rgba(255, 255, 255, 0.2);
                    border-top: 4px solid #e60012;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin-bottom: 16px;
                "></div>
                <div class="dos-title" style="
                    font-size: 18px;
                    font-weight: 500;
                    margin-bottom: 8px;
                    text-align: center;
                    line-height: 1.25;
                    padding: 0 8px;
                    max-width: 320px;
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                    hyphens: auto;
                ">Loading ${GAME_TITLE}</div>
                <div class="dos-subtitle" style="
                    font-size: 12px;
                    opacity: 0.7;
                    text-align: center;
                    line-height: 1.25;
                    padding: 0 8px;
                ">Please wait while the ROM loads...</div>
            </div>
        `;

        // Add responsive styles with class selectors
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            /* Tablet styles */
            @media (min-width: 640px) {
                .dos-spinner {
                    width: 64px !important;
                    height: 64px !important;
                    margin-bottom: 24px !important;
                }
                .dos-title {
                    font-size: 20px !important;
                    max-width: 384px !important;
                }
                .dos-subtitle {
                    font-size: 14px !important;
                }
            }
            
            /* Desktop styles */
            @media (min-width: 768px) {
                #dynamic-dos-loader > div {
                    padding: 32px !important;
                }
                .dos-spinner {
                    width: 80px !important;
                    height: 80px !important;
                    margin-bottom: 32px !important;
                }
                .dos-title {
                    font-size: 24px !important;
                    max-width: 448px !important;
                }
                .dos-subtitle {
                    font-size: 16px !important;
                }
            }
            
            /* Large desktop styles */
            @media (min-width: 1024px) {
                .dos-spinner {
                    width: 96px !important;
                    height: 96px !important;
                    margin-bottom: 40px !important;
                }
                .dos-title {
                    font-size: 28px !important;
                    max-width: 512px !important;
                }
                .dos-subtitle {
                    font-size: 18px !important;
                }
            }
        `;
        
        document.head.appendChild(style);
        container.appendChild(loader);
    }

    function hideDosIframeLoader() {
        const loader = document.getElementById('dynamic-dos-loader');
        if (loader && !isDosLoaderHidden()) {
            markDosLoaderAsHidden();
            loader.style.opacity = '0';
            loader.style.transition = 'opacity 0.5s ease-out';
            setTimeout(() => {
                loader.remove();
            }, 500);
        }
    }

    function removeDosLoaderIfHidden() {
        if (isDosLoaderHidden()) {
            const loader = document.getElementById('dynamic-dos-loader');
            if (loader) {
                loader.remove();
            }
        }
    }

    // Initialize loader
    function initDosLoader() {
        createDosLoader();
        removeDosLoaderIfHidden();
    }

    // Run immediately
    initDosLoader();

    // Fallback timeout (only if not already hidden)
    setTimeout(() => {
        if (!isDosLoaderHidden()) {
            console.log("DOS iframe loader fallback timeout - hiding loader");
            hideDosIframeLoader();
        }
    }, 20000);

    // Handle iframe load
    document.addEventListener('DOMContentLoaded', function() {
        const iframe = document.getElementById('game-iframe');
        if (iframe && !iframe.hasAttribute('data-dos-listener-added')) {
            iframe.setAttribute('data-dos-listener-added', 'true');
            iframe.addEventListener('load', hideDosIframeLoader);
        }
    });

    // Handle Livewire updates
    document.addEventListener('livewire:updated', function() {
        initDosLoader();
    });
</script>
@endif
