<x-container class="items-start pb-6">
    <div class="flex flex-col gap-y-4 max-play:gap-y-8 w-full">

        <!-- player & user data: game 16:9; side panel full viewport height on desktop -->
        <div class="play-stage flex flex-col gap-y-1 max-play:overflow-visible">
            <div class="play-game-rail w-full shrink-0 max-play:relative max-play:left-1/2 max-play:-translate-x-1/2 max-play:w-screen max-play:max-w-none">
                {{-- Sticky emulator dock (iframe + session bar); in-flow so chat top is always reachable --}}
                <div class="play-emulator-dock" wire:ignore>
                    <div id="game-container" class="play-game-frame relative w-full bg-black max-play:rounded-none rounded-t-lg overflow-hidden">
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
                    <div class="play-session-bar-slot shrink-0">
                        <x-play.session-bar
                            :console="$console"
                            :game="$game"
                            :save-slots-used="$save_slots_used"
                            :save-slots-total="$save_slots_total"
                            :save-slots-occupied="$save_slots_occupied"
                        />
                    </div>
                </div>

                {{-- Chat fills leftover viewport under the sticky dock; lobby scrolls below --}}
                <div class="play-viewport-column">
                    <div id="play-chat" class="play-chat-block flex min-h-0 flex-col max-play:px-3">
                        <div class="play-live-chat flex min-h-0 flex-col overflow-hidden rounded-md border border-cod-gray-400 bg-[#d0d2d8] shadow-sm shadow-cod-gray-500/15 dark:border-cod-gray-700 dark:bg-cod-gray-900/50 dark:shadow-black/40">
                            <div class="flex min-h-0 flex-1 flex-col overflow-hidden">
                                @livewire('chat', [
                                    'console_id' => $console->id,
                                    'game' => $game->toArray(),
                                ], 'play-chat-'.$console->id.'-'.$game->id)
                            </div>
                            <div class="play-chat-compose-row shrink-0 border-t border-cod-gray-400/80 dark:border-cod-gray-700">
                                <div class="relative w-full">
                                    <span class="pointer-events-none absolute inset-y-0 left-2 z-10 flex items-center text-cod-gray-500 dark:text-cod-gray-400">
                                        @auth
                                        <x-pixelarticon name="user" :size="20" />
                                        @else
                                        <x-pixelarticon name="login" :size="20" />
                                        @endauth
                                    </span>
                                    @auth
                                    <x-input
                                        type="text"
                                        wire:model.live.lazy="input"
                                        wire:keydown.enter="sendMessage"
                                        placeholder="New message here..."
                                        class="play-chat-compose h-9 w-full pl-9 pr-9 text-base"
                                    />
                                    <button
                                        type="button"
                                        wire:click="sendMessage"
                                        class="absolute inset-y-0 right-1.5 z-10 flex items-center text-rose-600 hover:text-rose-500 dark:text-rose-400 dark:hover:text-rose-300"
                                        aria-label="Send message"
                                        title="Send message"
                                    >
                                        <x-pixelarticon name="send" :size="20" />
                                    </button>
                                    @else
                                    <x-input
                                        type="text"
                                        :disabled="true"
                                        placeholder="Sign in to leave a message..."
                                        class="play-chat-compose h-9 w-full cursor-not-allowed pl-9 pr-9 text-base opacity-60"
                                        title="Sign in to leave a message"
                                    />
                                    <span class="pointer-events-none absolute inset-y-0 right-1.5 z-10 flex items-center text-cod-gray-500 opacity-40 dark:text-cod-gray-400">
                                        <x-pixelarticon name="send" :size="20" />
                                    </span>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <x-play.multiplayer-lobby :game="$game" />
            </div>

            <!-- game panel -->
            <div class="play-side-panel">

                <div class="play-side-panel-header shrink-0">
                    <div class="pb-3 lg:pb-2">
                        <div class="flex flex-col gap-y-1">
                            <div class="leading-none text-2xl text-cod-gray-950 dark:text-cod-gray-50">
                                {{ $game->title }}
                            </div>
                            <div class="leading-none text-cod-gray-700 dark:text-cod-gray-400">
                                {{ $game->publisher }}
                            </div>
                        </div>
                    </div>

                    @guest
                    @if (strtolower($console->short_name) !== 'pc')
                    {{-- Cloud Save CTA Banner (guests; above tabs, shared across tab panels) --}}
                    <div
                        class="pb-6 lg:pb-3 pt-3 lg:pt-0"
                        x-data="{ show: !sessionStorage.getItem('save-cta-dismissed') }"
                        x-show="show"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 scale-y-100"
                        x-transition:leave-end="opacity-0 scale-y-0"
                        x-cloak
                    >
                        <div class="relative flex flex-col items-stretch gap-2 rounded-lg border border-fuchsia-400/80 dark:border-fuchsia-900/40 bg-gradient-to-br from-fuchsia-300/45 via-[#d0d2d8] to-fuchsia-200/40 dark:from-fuchsia-950 dark:via-fuchsia-950 dark:to-fuchsia-900 px-3 py-2 shadow-md shadow-fuchsia-900/15 dark:shadow-black/90">
                            <div class="flex items-start gap-x-2 min-w-0">
                                <p class="min-w-0 flex-1 text-base leading-tight text-cod-gray-900 dark:text-cod-gray-100">
                                    <span class="text-base">Save your game progress in Cloud.</span>
                                    <span class="text-base"><br/>Get <span class=" text-base text-fuchsia-900 dark:text-fuchsia-400">5 free</span> save slots per game.</span>
                                </p>
                                <button
                                    @click="show = false; sessionStorage.setItem('save-cta-dismissed', '1')"
                                    class="flex-shrink-0 -mt-0.5_ -mr-1.5 p-0.5 rounded text-cod-gray-600 hover:text-cod-gray-900 dark:text-cod-gray-500 dark:hover:text-cod-gray-300 transition duration-200"
                                    aria-label="Dismiss"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <a href="{{ route('login') }}"
                                class="flex w-full items-center justify-center gap-x-1.5 rounded-md bg-fuchsia-700 hover:bg-fuchsia-600 dark:bg-fuchsia-600 dark:hover:bg-fuchsia-500 px-2 py-1 text-white text-sm tracking-wider _font-semibold transition duration-200 shadow shadow-black/30">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Sign up free
                            </a>
                        </div>
                    </div>
                    @endif
                    @endguest

                    <!-- tabs -->
                    <div class="flex items-center justify-between gap-x-1">
                        <x-tab-item wire:click="changeTab('info')" @click="$dispatch('loader-top-on')" :active="$tabs['info']">
                            Info
                        </x-tab-item>
                        @if ($igdb['has_media'] ?? false)
                        <x-tab-item wire:click="changeTab('media')" @click="$dispatch('loader-top-on')" :active="$tabs['media']">
                            Media
                        </x-tab-item>
                        @endif
                    </div>
                </div>

                <!-- info / media tabs -->
                <x-tab-content :contain="false">
                    @if ($tabs['info'])
                    <div class="flex flex-1 min-h-0 flex-col play:overflow-visible overflow-y-auto">
                    <div class="flex flex-col gap-y-4 p-4">
                        @if ($game->genres->isNotEmpty())
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 w-full">
                            @foreach ($game->genres as $genre)
                            <a
                                href="{{ route('genres', $genre->name) }}"
                                target="_genres_{{ $genre->name }}_{{ $loop->index }}"
                                class="inline-flex shrink-0"
                            >
                                <x-tag class="whitespace-nowrap text-left">#{{ $genre->name }}</x-tag>
                            </a>
                            @endforeach
                        </div>
                        @endif

                        <!-- header -->
                        <div class="flex flex-row-reverse items-start justify-center gap-x-4">
                            <div class="w-full">
                                <div class="flex flex-col gap-y-1">
                                    <div class="leading-none text-cod-gray-900 dark:text-cod-gray-200">
                                        {{ $console->long_name }}
                                    </div>
                                    <div class="flex items-center gap-x-1 justify-start">
                                        <div class="leading-none text-cod-gray-600 dark:text-cod-gray-400">
                                            {{ $game->release_year }}
                                        </div>
                                        <div class="text-cod-gray-500 dark:text-cod-gray-600">•</div>
                                        @if($game->rating)
                                        <div class="leading-none text-cod-gray-600 dark:text-cod-gray-400">
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
                                                <div class="leading-none text-fuchsia-700 dark:text-fuchsia-500">Not supported</div>
                                            @endif
                                        </div>
                                    </div>
                                    @endauth
                                    <div class="flex items-center gap-x-1 justify-start">
                                        <div class="leading-none text-cod-gray-900 dark:text-cod-gray-200">Multiplayer:</div>
                                        @if ($game->multiplayer_support)
                                        <div class="leading-none text-green-700 dark:text-green-500">Yes</div>
                                        @else
                                        <div class="leading-none text-fuchsia-700 dark:text-fuchsia-500">No</div>
                                        @endif
                                    </div>
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
                            <div class="text-cod-gray-900 dark:text-cod-gray-200 leading-tight">
                                {{ $game->description }}
                            </div>
                        </x-accordion>

                    </div>
                    </div>
                    @endif

                    {{-- Keep shell mounted after first Media visit so PiP survives Info/Media switches --}}
                    @if (($tabs['media'] || $media_shell_mounted) && ($igdb['has_media'] ?? false))
                    <div
                        wire:key="play-media-shell-{{ $game->id }}"
                        @class([
                            'flex flex-1 min-h-0 flex-col play:overflow-visible overflow-y-auto',
                            'hidden' => ! $tabs['media'],
                        ])
                        @if (! $tabs['media']) aria-hidden="true" @endif
                    >
                        <div class="flex flex-col gap-y-4 p-4">
                            @if ($igdb['has_videos'] ?? false)
                            <x-accordion wire:click="toggle('videos')" :toggler="$accordion_toggler['videos']">
                                <x-slot name="title">Videos</x-slot>
                                <x-play.video-carousel
                                    :videos="$igdb['videos']"
                                    :game-id="$game->id"
                                    :progress-url="auth()->check() ? route('player-data.youtube-progress.upsert', $game) : null"
                                    :progress="$video_progress"
                                    :csrf="csrf_token()"
                                    :can-sync="auth()->check()"
                                />
                            </x-accordion>
                            @endif

                            @if (! empty($igdb['artworks']))
                            <x-accordion wire:click="toggle('artworks')" :toggler="$accordion_toggler['artworks']">
                                <x-slot name="title">Artworks</x-slot>
                                <x-play.artworks :artworks="$igdb['artworks']" :game-title="$game->title" />
                            </x-accordion>
                            @endif

                            @if (! empty($igdb['similar_games']))
                            <x-accordion wire:click="toggle('similar')" :toggler="$accordion_toggler['similar']">
                                <x-slot name="title">Similar</x-slot>
                                <x-play.similar-games :games="$igdb['similar_games']" />
                            </x-accordion>
                            @endif
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
    if (window.__vintagePlayEmulatorSpacerSync) return
    window.__vintagePlayEmulatorSpacerSync = true

    function syncPlayEmulatorDock() {
        const dock = document.querySelector('.play-emulator-dock')
        const column = document.querySelector('.play-viewport-column')
        if (!dock) {
            document.documentElement.style.removeProperty('--play-dock-height')
            return
        }

        const dockH = dock.offsetHeight
        // Persist on <html> — Livewire morphs .play-stage and would wipe an inline var there,
        // briefly dropping --play-dock-height and exploding chat min-height.
        if (dockH > 0) {
            document.documentElement.style.setProperty('--play-dock-height', dockH + 'px')
        }

        const mobile = window.matchMedia('(max-width: 1079px)').matches
        if (mobile && column) {
            // Chat column fills whatever remains under the nav + in-flow dock.
            const navPx = 4 * 16 // top-spacer / fixed nav = 4rem
            const leftover = window.innerHeight - navPx - dockH
            column.style.height = Math.max(leftover, 0) + 'px'
            return
        }

        if (column) column.style.height = ''
    }

    function boot() {
        syncPlayEmulatorDock()
        const dock = document.querySelector('.play-emulator-dock')
        if (typeof ResizeObserver !== 'undefined' && dock) {
            new ResizeObserver(syncPlayEmulatorDock).observe(dock)
        }
        window.addEventListener('resize', syncPlayEmulatorDock)
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot)
    } else {
        boot()
    }
    document.addEventListener('livewire:navigated', syncPlayEmulatorDock)
    document.addEventListener('livewire:updated', syncPlayEmulatorDock)
})()
</script>
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
