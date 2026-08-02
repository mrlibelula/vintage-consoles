@props([
    'console',
    'game',
    'saveSlotsUsed' => 0,
    'saveSlotsTotal' => 5,
])

@php
    $short = strtolower($console->short_name);
    $isPc = $short === 'pc';
    $isArcade = $short === 'arcade';
@endphp

<div
    class="mt-1.5 rounded-md bg-cod-gray-200/70 dark:bg-cod-gray-900/70 px-2.5 py-2"
    x-data="{
        hotkeysOpen: false,
        dock: null,
        init() {
            this.dock = this.$el.closest('.play-emulator-dock')
        },
        openHotkeys() {
            this.hotkeysOpen = true
            if (this.dock) this.dock.style.zIndex = '90'
        },
        closeHotkeys() {
            this.hotkeysOpen = false
            if (this.dock) this.dock.style.zIndex = ''
        },
    }"
>
    <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 text-sm">
        <a
            wire:navigate
            href="{{ route('home', $short) }}"
            class="uppercase tracking-wide text-cod-gray-600 dark:text-cod-gray-400 hover:text-rose-700 dark:hover:text-rose-300 hover:underline"
        >
            {{ $isPc ? 'PC DOS' : $console->short_name }}
        </a>

        @if (! $isPc)
            @auth
                <span class="text-cod-gray-700 dark:text-cod-gray-300">
                    Slots
                    @if ($game->save_state_support)
                        <span class="text-green-800 dark:text-green-500">{{ $saveSlotsUsed }}/{{ $saveSlotsTotal }}</span>
                    @else
                        <span class="text-fuchsia-700 dark:text-fuchsia-500">n/a</span>
                    @endif
                </span>
            @else
                <span class="text-cod-gray-600 dark:text-cod-gray-400">Cloud saves · <a href="{{ route('login') }}" class="text-rose-700 dark:text-rose-300 hover:underline">sign in</a></span>
            @endauth
        @else
            <span class="text-cod-gray-600 dark:text-cod-gray-400">· no cloud slots</span>
        @endif

        @if ($isArcade)
            <span class="text-cod-gray-600 dark:text-cod-gray-400">Coin · Start below</span>
        @endif

        <button
            type="button"
            class="ml-auto text-lg text-rose-700 dark:text-rose-300 hover:underline"
            @click="openHotkeys()"
        >
            Hotkeys
        </button>
    </div>

    <div
        x-show="hotkeysOpen"
        x-cloak
        class="fixed inset-0 z-[90] flex items-center justify-center bg-black/70 p-4"
        @keydown.escape.window="closeHotkeys()"
        @click.self="closeHotkeys()"
    >
        <div class="w-full max-w-md rounded-lg border border-cod-gray-700 bg-cod-gray-950 p-4 shadow-xl text-cod-gray-100">
            <div class="mb-3 flex items-center justify-between gap-x-2">
                <h2 class="text-lg text-rose-300">Hotkeys</h2>
                <button type="button" class="text-sm text-cod-gray-400 hover:text-white" @click="closeHotkeys()">Close</button>
            </div>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-x-4"><dt class="font-mono text-cod-gray-300">F</dt><dd class="text-cod-gray-400">Fullscreen</dd></div>
                @if (! $isPc)
                <div class="flex justify-between gap-x-4"><dt class="font-mono text-cod-gray-300">P</dt><dd class="text-cod-gray-400">Pause / resume</dd></div>
                <div class="flex justify-between gap-x-4"><dt class="font-mono text-cod-gray-300">F2</dt><dd class="text-cod-gray-400">Save highlighted slot</dd></div>
                <div class="flex justify-between gap-x-4"><dt class="font-mono text-cod-gray-300">F4</dt><dd class="text-cod-gray-400">Load highlighted slot</dd></div>
                <div class="flex justify-between gap-x-4"><dt class="font-mono text-cod-gray-300">Ctrl+Alt+1–5</dt><dd class="text-cod-gray-400">Pick slot</dd></div>
                @endif
                @if ($isArcade)
                <div class="flex justify-between gap-x-4"><dt class="font-mono text-cod-gray-300">Insert Coin</dt><dd class="text-cod-gray-400">Coin (on-screen button)</dd></div>
                <div class="flex justify-between gap-x-4"><dt class="font-mono text-cod-gray-300">Start</dt><dd class="text-cod-gray-400">P1 Start (on-screen button)</dd></div>
                @endif
            </dl>
        </div>
    </div>
</div>
