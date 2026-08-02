@php
    $posterTitle = $game?->title ?? '';
@endphp

<div
    x-data="{
        title: @js($posterTitle),
        show: false,
        timer: null,
        tipStyle: '',
        positionTip() {
            const el = this.$refs.poster;
            if (!el) {
                return;
            }
            const r = el.getBoundingClientRect();
            this.tipStyle = 'left:' + (r.left + r.width / 2) + 'px;top:' + (r.bottom + 10) + 'px;';
        },
        scheduleShow() {
            if (!this.title) {
                return;
            }
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                this.positionTip();
                this.show = true;
            }, 700);
        },
        hideTip() {
            clearTimeout(this.timer);
            this.timer = null;
            this.show = false;
        },
    }"
    @mouseenter="scheduleShow()"
    @mouseleave="hideTip()"
    @scroll.window="if (show) hideTip()"
    class="relative w-full min-w-0 shrink-0"
>
    <button
        type="button"
        x-ref="poster"
        class="lazy-load-bg group block w-full rounded-lg overflow-hidden bg-center bg-cover bg-no-repeat border-[3px] shadow-md shadow-cod-gray-500 dark:shadow-black border-cod-gray-500 dark:border-cod-gray-700 opacity-75 hover:opacity-100 smooth-300 cursor-pointer"
        aria-label="{{ $posterTitle }}"
        data-bg-url="{{ $game?->poster }}"
        style="background-image: url({{ asset('images/placeholder-poster-homer.jpg') }});"
    >
        <div class="flex items-end justify-center w-full aspect-[8.5/12] overflow-hidden">
            @if($showConsoleLabel && isset($game->console) && $game->console)
            <div class="w-full bg-white/70 dark:bg-cod-gray-700/70 dark:text-gray-200 font-mono text-xs py-0.5 font-semibold">
                {{ $game->console->short_name === 'atari2600' ? 'Atari 2600' : $game->console->short_name }}
            </div>
            @else
            &nbsp;
            @endif
        </div>
    </button>

    @if ($posterTitle !== '')
    <template x-teleport="body">
        <div
            x-show="show"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            :style="tipStyle"
            class="pointer-events-none fixed z-[9999] -translate-x-1/2 max-w-[14rem] rounded border border-cod-gray-300 bg-white px-2 py-1 text-center text-xs font-mono text-cod-gray-900 shadow-md shadow-cod-gray-500/25 dark:border-cod-gray-600/80 dark:bg-cod-gray-900 dark:text-cod-gray-100 dark:shadow-black/40"
            x-text="title"
            role="tooltip"
        ></div>
    </template>
    @endif
</div>
