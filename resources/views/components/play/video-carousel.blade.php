@props([
    'videos' => [],
    'gameId' => null,
    'progressUrl' => null,
    'progress' => [],
    'csrf' => null,
    'canSync' => false,
    'compact' => false,
])

@if (! empty($videos))
<div
    wire:ignore
    class="w-full"
    x-data="vintageYoutubeMedia({
        videos: {{ Js::from($videos) }},
        gameId: {{ (int) $gameId }},
        progressUrl: {{ Js::from($progressUrl) }},
        serverProgress: {{ Js::from($progress) }},
        csrf: {{ Js::from($csrf) }},
        canSync: {{ $canSync ? 'true' : 'false' }},
        compact: {{ $compact ? 'true' : 'false' }},
    })"
>
    {{-- Off-screen host for duration probing (non-compact owner only) --}}
    <div x-ref="durationProbe" class="pointer-events-none absolute h-px w-px overflow-hidden opacity-0" aria-hidden="true"></div>

    @if ($compact)
        <div class="flex flex-col gap-y-2">
            <template x-for="(video, index) in videos" :key="video.youtube_id">
                <button
                    type="button"
                    class="flex w-full items-center gap-x-2 rounded-md bg-cod-gray-200/70 dark:bg-cod-gray-900/60 p-1.5 text-left hover:bg-cod-gray-300/80 dark:hover:bg-cod-gray-800 smooth-300"
                    @click="openPip(index)"
                >
                    <div class="relative w-20 shrink-0 overflow-hidden rounded aspect-video bg-black">
                        <img :src="video.thumb" :alt="video.title" class="h-full w-full object-cover" loading="lazy">
                        <span
                            x-show="resumeLabel(video.youtube_id)"
                            class="absolute bottom-0.5 left-0.5 rounded bg-black/80 px-1 text-[10px] leading-tight text-amber-200"
                            x-text="resumeLabel(video.youtube_id)"
                        ></span>
                        <span
                            x-show="durationLabel(video.youtube_id)"
                            class="absolute bottom-0.5 right-0.5 rounded bg-black/80 px-1 text-[10px] leading-tight text-white"
                            x-text="durationLabel(video.youtube_id)"
                        ></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm text-cod-gray-900 dark:text-cod-gray-100" x-text="video.title"></div>
                        <div class="text-xs text-cod-gray-600 dark:text-cod-gray-400">
                            <span x-text="video.source === 'walkthrough' ? 'Walkthrough' : 'IGDB'"></span>
                            <span x-show="durationLabel(video.youtube_id)" x-text="' · ' + durationLabel(video.youtube_id)"></span>
                        </div>
                    </div>
                </button>
            </template>
            <button
                type="button"
                class="mt-1 text-left text-sm text-rose-700 dark:text-rose-300 hover:underline"
                @click="scrollToMedia()"
            >
                See all media ↓
            </button>
        </div>
    @else
        <div class="flex flex-col gap-y-3">
            <div class="relative w-full overflow-hidden rounded-lg bg-black aspect-video">
                <div
                    x-show="!activeId || pipOpen"
                    class="absolute inset-0 z-[1] flex items-center justify-center text-cod-gray-400 text-sm pointer-events-none"
                >
                    <span x-text="pipOpen ? 'Playing in picture-in-picture' : 'Select a video'"></span>
                </div>
                {{-- Keep host laid out (not display:none) so YT can mount with real dimensions --}}
                <div
                    x-ref="inlineHost"
                    class="absolute inset-0 z-0"
                    :class="activeId && !pipOpen ? 'visible' : 'invisible pointer-events-none'"
                ></div>
                <div
                    x-show="playerLoading && activeId && !pipOpen"
                    x-cloak
                    class="absolute inset-0 z-[2] flex items-center justify-center bg-black/50 text-cod-gray-200 text-sm pointer-events-none"
                >
                    Loading…
                </div>
                <div
                    x-show="playerError && activeId && !pipOpen"
                    x-cloak
                    class="absolute inset-0 z-[2] flex items-center justify-center bg-black/70 px-3 text-center text-rose-300 text-sm"
                    x-text="playerError"
                ></div>
                <button
                    type="button"
                    x-show="activeId && !pipOpen"
                    x-cloak
                    class="absolute bottom-2 right-2 z-10 rounded bg-black/75 px-2 py-1 text-xs text-white hover:bg-black smooth-300"
                    @click="openPip(activeIndex)"
                >
                    Picture-in-picture
                </button>
            </div>
            <div
                x-show="activeId && activeMetaLabel"
                x-cloak
                class="truncate text-sm text-cod-gray-800 dark:text-cod-gray-300"
                x-text="activeMetaLabel"
            ></div>

            <swiper-container
                class="video-thumb-carousel w-full"
                slides-per-view="auto"
                space-between="8"
                free-mode="true"
                grab-cursor="true"
            >
                <template x-for="(video, index) in videos" :key="'slide-' + video.youtube_id">
                    <swiper-slide style="width: 8.5rem">
                        <div class="flex w-full flex-col">
                            {{-- Ring lives on the button; overflow stays on the inner media so the active border is not clipped --}}
                            <button
                                type="button"
                                class="group relative block w-full rounded-md bg-transparent aspect-video text-left"
                                @click="playInline(index)"
                                :class="activeIndex === index ? 'ring-2 ring-rose-500' : ''"
                                :aria-label="video.title"
                                :title="video.title"
                            >
                                <span class="absolute inset-0 overflow-hidden rounded-md bg-black">
                                    <img :src="video.thumb" :alt="video.title" class="h-full w-full object-cover opacity-90 group-hover:opacity-100" loading="lazy">
                                    <span class="absolute inset-0 flex items-center justify-center">
                                        <span class="rounded-full bg-black/60 p-1.5 text-white text-[10px]">▶</span>
                                    </span>
                                    <span
                                        x-show="resumeLabel(video.youtube_id)"
                                        class="absolute bottom-0.5 left-0.5 rounded bg-black/80 px-1 text-[10px] leading-tight text-amber-200"
                                        x-text="resumeLabel(video.youtube_id)"
                                    ></span>
                                    <span
                                        x-show="durationLabel(video.youtube_id)"
                                        class="absolute bottom-0.5 right-0.5 rounded bg-black/80 px-1 text-[10px] leading-tight text-white"
                                        x-text="durationLabel(video.youtube_id)"
                                    ></span>
                                </span>
                            </button>
                            <div
                                class="mt-1 w-full truncate text-xs leading-tight text-cod-gray-700 dark:text-cod-gray-300"
                                :class="activeIndex === index ? 'text-rose-700 dark:text-rose-300' : ''"
                                x-text="video.title"
                                :title="video.title"
                            ></div>
                        </div>
                    </swiper-slide>
                </template>
            </swiper-container>
        </div>
    @endif

    {{-- Shared PiP floater (one per page; only mount when this instance owns it) --}}
    <template x-teleport="body">
        <div
            x-ref="pip"
            class="fixed z-[80] w-[min(22rem,calc(100vw-1.5rem))] overflow-hidden rounded-lg border border-cod-gray-700 bg-cod-gray-950 shadow-2xl shadow-black/50"
            :class="pipOpen && ownsPip ? 'visible' : 'invisible pointer-events-none'"
            :style="`left:${pipX}px;top:${pipY}px`"
            :aria-hidden="!(pipOpen && ownsPip)"
        >
            <div
                class="flex cursor-move items-center justify-between gap-x-2 bg-cod-gray-900 px-2 py-1.5"
                @pointerdown="startDrag($event)"
            >
                <div class="min-w-0 truncate text-xs text-cod-gray-100" x-text="activeMetaLabel || activeTitle"></div>
                <div class="flex shrink-0 items-center gap-x-1">
                    <button type="button" class="rounded px-1.5 py-0.5 text-[11px] text-cod-gray-200 hover:bg-cod-gray-800" @click.stop="dockInline()">Dock</button>
                    <button type="button" class="rounded px-1.5 py-0.5 text-[11px] text-cod-gray-200 hover:bg-cod-gray-800" @click.stop="closePip()" aria-label="Close">✕</button>
                </div>
            </div>
            <div class="relative aspect-video bg-black">
                <div x-ref="pipHost" class="absolute inset-0"></div>
                <div
                    x-show="playerLoading && pipOpen"
                    x-cloak
                    class="absolute inset-0 z-[1] flex items-center justify-center bg-black/50 text-cod-gray-200 text-xs pointer-events-none"
                >
                    Loading…
                </div>
                <div
                    x-show="playerError && pipOpen"
                    x-cloak
                    class="absolute inset-0 z-[1] flex items-center justify-center bg-black/70 px-2 text-center text-rose-300 text-xs"
                    x-text="playerError"
                ></div>
            </div>
        </div>
    </template>
</div>
@endif
