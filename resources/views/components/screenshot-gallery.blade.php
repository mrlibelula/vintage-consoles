{{--
  Alpine.js screenshot gallery component.
  Props:
    - $screenshots : Collection<Screenshot> (must be eager-loaded, ordered by position)
    - $gameTitle   : string  (for alt text / modal heading)
    - $layout      : string  (admin|player)
--}}
@props([
    'screenshots',
    'gameTitle' => 'Game',
    'layout' => 'admin',
])

@if($screenshots->isNotEmpty())
<div
    x-data="{
        open: false,
        current: 0,
        total: {{ $screenshots->count() }},
        thumbs: {{ $screenshots->map(fn($s) => ['thumb' => $s->thumb_url, 'full' => $s->full_url])->toJson() }},
        imageSrc: '',
        imageLoading: false,
        gameTitle: @js($gameTitle),

        fullSrc(index) {
            const shot = this.thumbs[index];
            if (!shot) {
                return '';
            }

            return shot.full || shot.thumb || '';
        },
        stageImage(index) {
            const nextSrc = this.fullSrc(index);

            if (!nextSrc) {
                this.imageSrc = '';
                this.imageLoading = false;
                return;
            }

            if (this.imageSrc === nextSrc) {
                this.imageLoading = false;
                return;
            }

            this.imageLoading = true;

            const preload = new Image();
            preload.onload = () => {
                if (this.current !== index || !this.open) {
                    return;
                }

                this.imageSrc = nextSrc;
                this.imageLoading = false;
            };
            preload.onerror = () => {
                if (this.current !== index || !this.open) {
                    return;
                }

                this.imageSrc = this.thumbs[index]?.thumb || nextSrc;
                this.imageLoading = false;
            };
            preload.src = nextSrc;
        },
        openAt(index) {
            this.current = index;
            this.open = true;
            document.body.style.overflow = 'hidden';
            window.dispatchEvent(new CustomEvent('screenshot-gallery-open'));
            this.stageImage(index);
            this.$nextTick(() => {
                this.$refs.dialog?.focus({ preventScroll: true });
            });
        },
        close() {
            this.open = false;
            this.imageSrc = '';
            this.imageLoading = false;
            document.body.style.overflow = '';
            window.dispatchEvent(new CustomEvent('screenshot-gallery-close'));
        },
        prev() {
            this.current = this.current > 0 ? this.current - 1 : this.total - 1;
            this.stageImage(this.current);
        },
        next() {
            this.current = this.current < this.total - 1 ? this.current + 1 : 0;
            this.stageImage(this.current);
        },
    }"
    @keydown.escape.window.capture="if (open) { $event.preventDefault(); $event.stopImmediatePropagation(); close(); }"
>
    <x-screenshot-carousel
        :screenshots="$screenshots"
        :game-title="$gameTitle"
        :layout="$layout"
    />

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
            aria-label="{{ $gameTitle }} screenshots"
        >
            <div class="flex min-h-full items-stretch justify-center sm:items-center sm:px-6 sm:py-8">
                <div
                    class="fixed inset-0 transition-opacity"
                    aria-hidden="true"
                    @click="close()"
                >
                    <div class="absolute inset-0 bg-black/55 backdrop-blur-[4.5px] backdrop-brightness-[.4]"></div>
                </div>

                <div
                    class="relative z-10 flex h-dvh w-full flex-col overflow-hidden rounded-none border-0 bg-cod-gray-950 text-left shadow-2xl shadow-black/80 sm:h-[min(85vh,56rem)] sm:w-full sm:max-w-5xl sm:rounded-xl sm:border sm:border-cod-gray-700/80 sm:bg-cod-gray-950/95"
                    @click.stop
                >
                    <div class="flex shrink-0 items-center justify-between border-b border-cod-gray-800 px-4 py-3 sm:px-6">
                        <div class="text-sm text-cod-gray-300">
                            <span class="font-medium text-white">{{ $gameTitle }}</span>
                            &nbsp;—&nbsp;
                            <span x-text="(current + 1) + ' / ' + total"></span>
                        </div>
                        <button
                            @click="close()"
                            class="p-1 text-cod-gray-400 transition-colors hover:text-white"
                            aria-label="Close gallery"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="relative min-h-0 flex-1 bg-black/40">
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
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            :src="imageSrc"
                            :alt="gameTitle + ' screenshot ' + (current + 1)"
                            class="absolute inset-0 z-0 h-full w-full object-contain p-4 sm:p-8 [image-rendering:pixelated]"
                            decoding="async"
                            fetchpriority="high"
                        >

                        <button
                            @click.stop="prev()"
                            class="absolute left-2 top-1/2 z-20 -translate-y-1/2 rounded-full bg-black/40 p-2 text-cod-gray-300 transition-colors hover:text-purple-400 sm:left-4"
                            aria-label="Previous screenshot"
                        >
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <button
                            @click.stop="next()"
                            class="absolute right-2 top-1/2 z-20 -translate-y-1/2 rounded-full bg-black/40 p-2 text-cod-gray-300 transition-colors hover:text-purple-400 sm:right-4"
                            aria-label="Next screenshot"
                        >
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex shrink-0 items-center justify-center gap-x-1.5 border-t border-cod-gray-800 px-4 py-4">
                        <template x-for="(_, idx) in thumbs" :key="idx">
                            <button
                                @click.stop="current = idx; stageImage(idx)"
                                :class="idx === current ? 'scale-125 bg-white' : 'bg-cod-gray-600 hover:bg-cod-gray-400'"
                                class="h-2 w-2 rounded-full transition-all duration-200"
                                :aria-label="'Go to screenshot ' + (idx + 1)"
                            ></button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@else
<div class="flex flex-col cursor-default rounded-xl py-6 w-full text-cod-gray-300">
    <div class="text-center text-3xl">¯\_(ツ)_/¯</div>
</div>
@endif
