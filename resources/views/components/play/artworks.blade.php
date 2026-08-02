@props(['artworks' => [], 'gameTitle' => 'Game'])

@if (! empty($artworks))
<div
    wire:ignore
    x-data="{
        open: false,
        current: 0,
        total: {{ count($artworks) }},
        items: {{ Js::from($artworks) }},
        imageSrc: '',
        imageLoading: false,

        fullSrc(index) {
            const art = this.items[index];
            if (!art) return '';
            return art.full || art.thumb || '';
        },
        stage(index) {
            const nextSrc = this.fullSrc(index);
            if (!nextSrc) {
                this.imageSrc = '';
                this.imageLoading = false;
                return;
            }

            this.imageLoading = true;
            // Show thumb immediately so the dialog never looks empty while HD loads.
            this.imageSrc = this.items[index]?.thumb || nextSrc;

            const preload = new Image();
            preload.onload = () => {
                if (this.current !== index || !this.open) return;
                this.imageSrc = nextSrc;
                this.imageLoading = false;
            };
            preload.onerror = () => {
                if (this.current !== index || !this.open) return;
                this.imageSrc = this.items[index]?.thumb || nextSrc;
                this.imageLoading = false;
            };
            preload.src = nextSrc;
        },
        openAt(index) {
            this.current = index;
            this.open = true;
            document.body.style.overflow = 'hidden';
            this.stage(index);
            this.$nextTick(() => {
                this.$refs.dialog?.focus({ preventScroll: true });
            });
        },
        close() {
            this.open = false;
            this.imageSrc = '';
            this.imageLoading = false;
            document.body.style.overflow = '';
        },
        prev() {
            this.current = this.current > 0 ? this.current - 1 : this.total - 1;
            this.stage(this.current);
        },
        next() {
            this.current = this.current < this.total - 1 ? this.current + 1 : 0;
            this.stage(this.current);
        },
    }"
    @keydown.escape.window.capture="if (open) { $event.preventDefault(); $event.stopImmediatePropagation(); close(); }"
    class="w-full"
>
    <swiper-container
        class="block w-full"
        slides-per-view="auto"
        space-between="8"
        free-mode="true"
        grab-cursor="true"
        prevent-clicks="false"
        threshold="5"
    >
        @foreach ($artworks as $i => $art)
            <swiper-slide style="width: 11rem">
                <button
                    type="button"
                    class="block w-full overflow-hidden rounded-md aspect-video bg-black focus:outline-none focus:ring-2 focus:ring-rose-500"
                    @click.stop="openAt({{ $i }})"
                    aria-label="Open {{ $gameTitle }} artwork {{ $i + 1 }}"
                >
                    <img
                        src="{{ $art['thumb'] }}"
                        alt="{{ $gameTitle }} artwork {{ $i + 1 }}"
                        class="h-full w-full object-cover hover:brightness-110 smooth-300"
                        loading="lazy"
                        decoding="async"
                    >
                </button>
            </swiper-slide>
        @endforeach
    </swiper-container>

    <template x-teleport="body">
        <div
            x-show="open"
            x-ref="dialog"
            tabindex="-1"
            @keydown.arrow-left.stop.prevent="prev()"
            @keydown.arrow-right.stop.prevent="next()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-cloak
            class="fixed inset-0 z-[100] overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-label="{{ $gameTitle }} artworks"
        >
            <div class="flex min-h-full items-end justify-center px-4 pb-4 pt-16 text-center sm:items-center sm:px-6 sm:py-8">
                <div
                    class="fixed inset-0 transition-opacity"
                    aria-hidden="true"
                    @click="close()"
                >
                    <div class="absolute inset-0 bg-black/55 backdrop-blur-[4.5px] backdrop-brightness-[.4]"></div>
                </div>

                <div
                    class="relative z-10 flex w-full max-w-5xl flex-col overflow-hidden rounded-xl border border-cod-gray-700/80 bg-cod-gray-950/95 text-left shadow-2xl shadow-black/80"
                    @click.stop
                >
                    <div class="flex items-center justify-between border-b border-cod-gray-800 px-4 py-3 sm:px-6">
                        <div class="text-sm text-cod-gray-300">
                            <span class="font-medium text-white">{{ $gameTitle }}</span>
                            &nbsp;—&nbsp;
                            <span x-text="(current + 1) + ' / ' + total"></span>
                        </div>
                        <button
                            type="button"
                            @click="close()"
                            class="p-1 text-cod-gray-400 transition-colors hover:text-white"
                            aria-label="Close gallery"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="relative flex min-h-[45vh] items-center justify-center bg-black/40 px-4 py-6 sm:min-h-[60vh] sm:px-8 sm:py-8">
                        <div
                            x-show="imageLoading"
                            class="absolute inset-0 z-10 flex items-center justify-center pointer-events-none"
                            aria-hidden="true"
                        >
                            <div class="h-12 w-12 animate-spin rounded-full border-2 border-cod-gray-600 border-t-white"></div>
                        </div>

                        <img
                            x-show="imageSrc"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-[0.98]"
                            x-transition:enter-end="opacity-100 scale-100"
                            :src="imageSrc"
                            :alt="'{{ addslashes($gameTitle) }} artwork ' + (current + 1)"
                            class="relative z-0 max-h-[55vh] w-full max-w-full object-contain sm:max-h-[70vh]"
                            decoding="async"
                            fetchpriority="high"
                        >

                        @if (count($artworks) > 1)
                            <button
                                type="button"
                                @click.stop="prev()"
                                class="absolute left-2 top-1/2 z-20 -translate-y-1/2 rounded-full bg-black/40 p-2 text-cod-gray-300 transition-colors hover:text-rose-400 sm:left-4"
                                aria-label="Previous artwork"
                            >
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                @click.stop="next()"
                                class="absolute right-2 top-1/2 z-20 -translate-y-1/2 rounded-full bg-black/40 p-2 text-cod-gray-300 transition-colors hover:text-rose-400 sm:right-4"
                                aria-label="Next artwork"
                            >
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        @endif
                    </div>

                    @if (count($artworks) > 1)
                        <div class="flex items-center justify-center gap-x-1.5 border-t border-cod-gray-800 px-4 py-4">
                            <template x-for="(_, idx) in items" :key="idx">
                                <button
                                    type="button"
                                    @click.stop="current = idx; stage(idx)"
                                    :class="idx === current ? 'scale-125 bg-white' : 'bg-cod-gray-600 hover:bg-cod-gray-400'"
                                    class="h-2 w-2 rounded-full transition-all duration-200"
                                    :aria-label="'Go to artwork ' + (idx + 1)"
                                ></button>
                            </template>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>
@endif
