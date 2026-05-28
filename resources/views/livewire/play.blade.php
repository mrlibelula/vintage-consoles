<x-container class="h-screen -mt-[4rem]">
    <div class="flex flex-col gap-y-8">

        @guest
        @if (strtolower($console->short_name) !== 'pc')
        {{-- Cloud Save CTA Banner --}}
        <div
            x-data="{ show: !sessionStorage.getItem('save-cta-dismissed') }"
            x-show="show"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-y-100"
            x-transition:leave-end="opacity-0 scale-y-0"
            x-cloak
            class="-mb-4"
        >
            <div class="relative flex flex-col sm:flex-row items-center justify-between gap-3 rounded-lg border border-purple-200/80 dark:border-purple-900/40 bg-gradient-to-r from-white via-purple-50/70 to-white dark:from-cod-gray-950 dark:via-purple-950/20 dark:to-cod-gray-950 px-4 py-3 shadow-lg shadow-black/10 dark:shadow-black/40">
                <div class="flex items-center gap-x-3 min-w-0">
                    <div class="flex-shrink-0 flex items-center justify-center w-9 h-9 rounded-full bg-purple-500/10 dark:bg-purple-600/15 border border-purple-300/70 dark:border-purple-600/25">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6H16a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-x-1.5 min-w-0">
                        <span class="text-cod-gray-900 dark:text-cod-gray-100 text-sm font-semibold leading-tight">Save your game progress in the cloud</span>
                        <span class="text-cod-gray-600 dark:text-cod-gray-400 text-xs sm:text-sm leading-tight">— 5 free slots per game, always free.</span>
                    </div>
                </div>
                <div class="flex items-center gap-x-2 flex-shrink-0">
                    <a href="{{ route('login') }}"
                        class="flex items-center gap-x-1.5 rounded-md bg-purple-600 hover:bg-purple-500 px-3 py-1.5 text-white text-xs font-semibold transition duration-200 shadow shadow-black/30">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Sign up free
                    </a>
                    <button
                        @click="show = false; sessionStorage.setItem('save-cta-dismissed', '1')"
                        class="ml-1 flex-shrink-0 p-1 rounded text-cod-gray-500 hover:text-cod-gray-800 dark:hover:text-cod-gray-300 transition duration-200"
                        aria-label="Dismiss"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endif
        @endguest

        <!-- player & user data -->
        <div class="flex flex-col gap-x-0 gap-y-1 xl:gap-x-2 xl:gap-y-0 xl:flex-row sticky items-start justify-between max-xl:overflow-visible xl:overflow-hidden">
            <div class="w-full shrink-0 max-xl:relative max-xl:left-1/2 max-xl:-translate-x-1/2 max-xl:w-screen max-xl:max-w-none xl:left-0 xl:translate-x-0 xl:w-[70%]">
            <div id="game-container" class="w-full bg-black h-full max-xl:rounded-none rounded-lg overflow-hidden relative">
                <iframe id="game-iframe" class="game-arena" frameborder="0" scrolling="no"
                    allow="fullscreen"
                    @if (strtolower($console->short_name) === 'pc')
                    src="{{ route('dosplayer', [
                        \App\Service\Tool::encode(json_encode($game->toPlayerPayload())),
                        strtolower($console->short_name),
                    ]) }}"
                    onload="hideDosIframeLoader()"
                    @else
                    src="{{ $player_route }}"
                    @endif>
                </iframe>
            </div>
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
                            <div class="w-[38%]">
                                @if ($game->cartridge)
                                <a wire:navigate href="/{{ $console->short_name }}" class="bg-black flex flex-col-reverse items-center justify-center overflow-hidden rounded-md shadow-md shadow-black">
                                    <img class="w-full" src="{{ $game->cartridge }}" alt="{{ $game->title }}">
                                    <div class="my-2 flex justify-center">
                                        <img class="w-[75%]" src="{{ $console->console_logo }}" alt="{{ $console->short_name }}">
                                    </div>
                                </a>
                                @else
                                <a wire:navigate href="/{{ $console->short_name }}" class="bg-black flex flex-col-reverse items-center justify-center overflow-hidden rounded-md shadow-md shadow-black">
                                    <img class="w-full" src="{{ $game->poster }}" alt="{{ $game->title }}">
                                    <div class="my-2 flex justify-center">
                                        <img class="w-[75%]" src="{{ $console->console_logo }}" alt="{{ $console->short_name }}">
                                    </div>
                                </a>
                                @endif
                            </div>

                            <div class="w-[62%]">
                                <div class="flex flex-col gap-y-1">
                                    <div class="leading-none text-2xl text-black dark:text-cod-gray-50">
                                        {{ $game->title }}
                                    </div>
                                    <div class="leading-none text-cod-gray-900 dark:text-cod-gray-400">
                                        {{ $game->publisher }}
                                    </div>
                                    <div class="flex items-center gap-x-1 justify-start">
                                        <div class="leading-none text-cod-gray-900 dark:text-cod-gray-200">
                                            {{ $game->release_year }}
                                        </div>
                                        <div class="text-cod-gray-900 dark:text-cod-gray-500">•</div>
                                        @if($game->rating)
                                        <div class="leading-none text-cod-gray-900 dark:text-cod-gray-200">
                                            {{ number_format($game->rating * 100, 0) }}%
                                        </div>
                                        @endif
                                    </div>
                                    @auth
                                    <div class="flex flex-col gap-y-1 items-start justify-start">
                                        <div class="flex items-center gap-x-1 justify-start">
                                            <div class="leading-none text-cod-gray-900 dark:text-cod-gray-400">Save slots:</div>
                                            @if ($game->save_state_support)
                                                <div class="leading-none text-green-900 dark:text-green-500">
                                                    {{ $save_slots_used }}/{{ $save_slots_total }}
                                                </div>
                                            @else
                                                <div class="leading-none text-purple-700 dark:text-purple-500">Not supported</div>
                                            @endif
                                        </div>
                                    </div>
                                    @endauth
                                    <div class="flex items-center gap-x-1 justify-start">
                                        <div class="leading-none text-cod-gray-900 dark:text-cod-gray-400">Multiplayer:</div>
                                        @if ($game->multiplayer_support)
                                        <div class="leading-none text-green-700 dark:text-green-500">Yes</div>
                                        @else
                                        <div class="leading-none text-purple-700 dark:text-purple-500">No</div>
                                        @endif
                                    </div>
                                    @if ($game->genres->isNotEmpty())
                                    <div class="flex gap-x-3 gap-1.5">
                                        @foreach ($game->genres as $genre)
                                        <a href="{{ route('genres', $genre->name) }}" target="_genres_{{ $genre->name }}_{{ $loop->index }}">
                                            <x-tag class="text-left">#{{ $genre->name }}</x-tag>
                                        </a>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- screenshots (Alpine-only gallery) -->
                        <x-accordion wire:click="toggle('screenshots')" :toggler="$accordion_toggler['screenshots']">
                            <x-slot name="title">Screenshots</x-slot>
                            <x-screenshot-gallery
                                :screenshots="$game->screenshots"
                                :game-title="$game->title"
                                layout="player"
                            />
                        </x-accordion>

                        <!-- description -->
                        <x-accordion wire:click="toggle('description')" :toggler="$accordion_toggler['description']">
                            <x-slot name="title">Description</x-slot>
                            {{ $game->description }}
                        </x-accordion>

                    </div>
                    @endif

                    @if ($tabs['chat'])
                    <div class="h-[90%]">
                        @livewire('chat', [
                            'console_id' => $console->id,
                            'game' => $game->toArray(),
                        ], uniqid())
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
    </div>
</x-container>

@push('scripts')
<script>
(function () {
    if (window.__vintagePlayPageFullscreenHotkey) return
    window.__vintagePlayPageFullscreenHotkey = true

    function vintagePlayPageEditableTarget(el) {
        if (!el || !(el instanceof HTMLElement)) return false
        if (el.closest('[contenteditable="true"]')) return true
        const t = el.tagName
        return t === 'INPUT' || t === 'TEXTAREA' || t === 'SELECT'
    }

    function vintagePlayPageIsFKey(event) {
        return !event.repeat && (event.code === 'KeyF' || event.key === 'f' || event.key === 'F')
    }

    function vintagePlayPageToggleIframeFullscreen() {
        const iframe = document.getElementById('game-iframe')
        if (!iframe) return
        const doc = document
        const fs = doc.fullscreenElement || doc.webkitFullscreenElement
        if (fs) {
            const exit = doc.exitFullscreen || doc.webkitExitFullscreen
            exit && exit.call(doc)
            return
        }
        const req = iframe.requestFullscreen || iframe.webkitRequestFullscreen
        req && req.call(iframe).catch(function () {})
    }

    window.addEventListener('message', function (event) {
        if (event.origin !== window.location.origin) return
        if (!event.data || event.data.type !== 'vintage-player-toggle-fullscreen') return
        vintagePlayPageToggleIframeFullscreen()
    })

    window.addEventListener('keydown', function (event) {
        if (event.defaultPrevented) return
        if (event.ctrlKey || event.metaKey || event.altKey) return
        if (!vintagePlayPageIsFKey(event)) return
        if (vintagePlayPageEditableTarget(event.target)) return
        const iframe = document.getElementById('game-iframe')
        if (!iframe) return
        event.preventDefault()
        vintagePlayPageToggleIframeFullscreen()
    }, true)
})()
</script>
@endpush

@if (strtolower($console->short_name) === 'pc')
<script>
    const STORAGE_KEY = 'dos_loader_{{ $game->id }}_hidden';
    const GAME_TITLE = @json($game->title);

    function isDosLoaderHidden() { return sessionStorage.getItem(STORAGE_KEY) === 'true'; }
    function markDosLoaderAsHidden() { sessionStorage.setItem(STORAGE_KEY, 'true'); }

    function createDosLoader() {
        if (isDosLoaderHidden()) return;
        if (document.getElementById('dynamic-dos-loader')) return;
        const container = document.getElementById('game-container');
        if (!container) return;
        const loader = document.createElement('div');
        loader.id = 'dynamic-dos-loader';
        loader.innerHTML = `<div style="position:absolute;top:0;left:0;width:100%;height:100%;background:#000;display:flex;flex-direction:column;justify-content:center;align-items:center;z-index:50;color:white;font-family:system-ui,sans-serif;padding:16px"><div style="width:64px;height:64px;border:4px solid rgba(255,255,255,0.2);border-top:4px solid #e60012;border-radius:50%;animation:spin 1s linear infinite;margin-bottom:16px"></div><div style="font-size:20px;font-weight:500;margin-bottom:8px;text-align:center">Loading ${GAME_TITLE}</div><div style="font-size:13px;opacity:0.7;text-align:center">Please wait while the ROM loads...</div></div>`;
        const style = document.createElement('style');
        style.textContent = '@keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}';
        document.head.appendChild(style);
        container.appendChild(loader);
    }

    function hideDosIframeLoader() {
        const loader = document.getElementById('dynamic-dos-loader');
        if (loader && !isDosLoaderHidden()) {
            markDosLoaderAsHidden();
            loader.style.opacity = '0';
            loader.style.transition = 'opacity 0.5s ease-out';
            setTimeout(() => loader.remove(), 500);
        }
    }

    createDosLoader();
    if (isDosLoaderHidden()) {
        const loader = document.getElementById('dynamic-dos-loader');
        if (loader) loader.remove();
    }

    setTimeout(() => { if (!isDosLoaderHidden()) hideDosIframeLoader(); }, 20000);

    document.addEventListener('DOMContentLoaded', function() {
        const iframe = document.getElementById('game-iframe');
        if (iframe && !iframe.hasAttribute('data-dos-listener-added')) {
            iframe.setAttribute('data-dos-listener-added', 'true');
            iframe.addEventListener('load', hideDosIframeLoader);
        }
    });

    document.addEventListener('livewire:updated', function() {
        createDosLoader();
        if (isDosLoaderHidden()) {
            const loader = document.getElementById('dynamic-dos-loader');
            if (loader) loader.remove();
        }
    });
</script>
@endif
