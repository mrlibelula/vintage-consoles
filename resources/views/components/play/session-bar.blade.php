@props([
    'console',
    'game',
    'saveSlotsUsed' => 0,
    'saveSlotsTotal' => 5,
    'saveSlotsOccupied' => [],
])

@php
    $short = strtolower($console->short_name);
    $isPc = $short === 'pc';
    $isArcade = $short === 'arcade';
    $showSlotDots = ! $isPc && auth()->check() && $game->save_state_support;
    $occupiedSlots = collect($saveSlotsOccupied)
        ->map(fn ($slot) => (int) $slot)
        ->filter(fn ($slot) => $slot >= 1)
        ->unique()
        ->values()
        ->all();
@endphp

<div
    class="play-session-bar"
    x-data="{
        hotkeysOpen: false,
        dock: null,
        selectedSlot: 1,
        slotTotal: {{ (int) $saveSlotsTotal }},
        usedSlots: @js($occupiedSlots),
        slotMessageHandler: null,
        isUsed(slot) {
            return this.usedSlots.includes(Number(slot))
        },
        slotDotClass(slot) {
            if (slot === this.selectedSlot) {
                return 'bg-green-500 dark:bg-green-400 ring-2 ring-green-300/70 dark:ring-green-200/50'
            }
            if (this.isUsed(slot)) {
                return 'bg-green-900/80 dark:bg-green-700/70 ring-1 ring-green-800/60 dark:ring-green-600/50'
            }
            return 'bg-transparent ring-2 ring-cod-gray-500 dark:ring-cod-gray-600 hover:ring-cod-gray-400 dark:hover:ring-cod-gray-500'
        },
        slotAriaLabel(slot) {
            const state = slot === this.selectedSlot ? 'selected' : (this.isUsed(slot) ? 'used' : 'empty')
            return 'Slot ' + slot + ', ' + state
        },
        onSlotMessage(event) {
            if (event.origin !== window.location.origin) return
            if (!event.data || event.data.type !== 'vintage-player-slot-selected') return
            const slot = Number(event.data.slot) || 1
            const total = Number(event.data.total) || this.slotTotal
            this.slotTotal = Math.max(1, total)
            this.selectedSlot = Math.min(Math.max(slot, 1), this.slotTotal)
            if (Array.isArray(event.data.used)) {
                this.usedSlots = event.data.used
                    .map((value) => Number(value))
                    .filter((value) => value >= 1 && value <= this.slotTotal)
            }
        },
        selectSlot(slot) {
            const next = Math.min(Math.max(Number(slot) || 1, 1), this.slotTotal)
            this.selectedSlot = next
            const iframe = document.getElementById('game-iframe')
            if (!iframe?.contentWindow) return
            try {
                iframe.contentWindow.postMessage({
                    type: 'vintage-player-select-slot',
                    slot: next,
                }, window.location.origin)
            } catch (_error) {}
        },
        sendArcade(action, down) {
            const iframe = document.getElementById('game-iframe')
            if (!iframe?.contentWindow || !action) return
            // Prefer RetroPad simulateInput in the player (select=coin, start=P1).
            // Do not synthesize Digit1/5 — EmulatorJS binds 1/2/3 to quick-save hotkeys.
            iframe.contentWindow.postMessage({
                type: 'vintage-arcade-key',
                action,
                down,
            }, window.location.origin)
        },
        pressArcade(action, event) {
            event.preventDefault()
            this.sendArcade(action, true)
        },
        releaseArcade(action, event) {
            event.preventDefault()
            this.sendArcade(action, false)
        },
        init() {
            this.dock = this.$el.closest('.play-emulator-dock')
            this.slotMessageHandler = (event) => this.onSlotMessage(event)
            window.addEventListener('message', this.slotMessageHandler)
            return () => {
                if (this.slotMessageHandler) {
                    window.removeEventListener('message', this.slotMessageHandler)
                }
            }
        },
        openHotkeys() {
            this.hotkeysOpen = true
            if (this.dock) this.dock.style.zIndex = '90'
        },
        closeHotkeys() {
            this.hotkeysOpen = false
            if (this.dock) this.dock.style.zIndex = ''
        },
        jumpTo(id) {
            const el = document.getElementById(id)
            if (!el) return
            // Sticky dock: scroll-margin on targets accounts for nav + dock height
            el.scrollIntoView({ behavior: 'smooth', block: 'start' })
        },
    }"
>
    <div class="play-session-bar__meta">
        <div class="flex min-w-0 items-center gap-x-2 whitespace-nowrap">
            <a
                wire:navigate
                href="{{ route('home', $short) }}"
                class="uppercase tracking-wide text-cod-gray-600 dark:text-cod-gray-400 hover:text-rose-700 dark:hover:text-rose-300 hover:underline"
            >
                {{ $isPc ? 'PC DOS' : $console->short_name }}
            </a>

            @if ($isArcade)
                <span class="inline-flex items-center gap-x-1">
                    <button
                        type="button"
                        class="select-none rounded border border-rose-700/70 dark:border-rose-400/60 px-1.5 py-0.5 text-xs leading-none text-rose-700 dark:text-rose-300 hover:bg-rose-700/10 dark:hover:bg-rose-400/10"
                        @pointerdown="pressArcade('coin', $event)"
                        @pointerup="releaseArcade('coin', $event)"
                        @pointerleave="releaseArcade('coin', $event)"
                        @pointercancel="releaseArcade('coin', $event)"
                    >Coin</button>
                    <button
                        type="button"
                        class="select-none rounded border border-cod-gray-500 dark:border-cod-gray-500 px-1.5 py-0.5 text-xs leading-none text-cod-gray-700 dark:text-cod-gray-300 hover:bg-cod-gray-500/10 dark:hover:bg-cod-gray-400/10"
                        @pointerdown="pressArcade('start', $event)"
                        @pointerup="releaseArcade('start', $event)"
                        @pointerleave="releaseArcade('start', $event)"
                        @pointercancel="releaseArcade('start', $event)"
                    >Start</button>
                </span>
            @endif

            @if ($isPc)
                <span class="text-cod-gray-600 dark:text-cod-gray-400">· no cloud slots</span>
            @elseif (! auth()->check())
                <span class="text-cod-gray-600 dark:text-cod-gray-400">· Cloud saves · <a href="{{ route('login') }}" class="text-rose-700 dark:text-rose-300 hover:underline">Sign in</a></span>
            @elseif (! $game->save_state_support)
                <span class="text-cod-gray-600 dark:text-cod-gray-400">Slots <span class="text-fuchsia-700 dark:text-fuchsia-500">n/a</span></span>
            @endif
        </div>

        @if ($showSlotDots)
            <div class="pointer-events-none absolute inset-y-0 left-1/2 flex -translate-x-1/2 items-center">
                <div class="pointer-events-auto inline-flex items-center gap-x-2 text-cod-gray-700 dark:text-cod-gray-300">
                    <span>Slots</span>
                    <div
                        class="inline-flex items-center gap-x-1.5"
                        role="group"
                        :aria-label="'Slot ' + selectedSlot + ' of ' + slotTotal + ' selected'"
                    >
                        <template x-for="n in slotTotal" :key="n">
                            <button
                                type="button"
                                class="inline-block h-3 w-3 rounded-full transition duration-150 hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500/60"
                                :class="slotDotClass(n)"
                                :aria-label="slotAriaLabel(n)"
                                :aria-pressed="n === selectedSlot ? 'true' : 'false'"
                                :title="slotAriaLabel(n)"
                                @click="selectSlot(n)"
                            ></button>
                        </template>
                    </div>
                    <span class="text-cod-gray-600 dark:text-cod-gray-500" aria-hidden="true">·</span>
                </div>
            </div>
        @endif

        <nav class="play-session-bar__jumps" aria-label="Quick jumps">
            <a
                href="#play-chat"
                class="play-session-bar__jump"
                @click.prevent="jumpTo('play-chat')"
            >Chat</a>
            <span class="text-cod-gray-600 dark:text-cod-gray-500" aria-hidden="true">·</span>
            <a
                href="#play-multiplayer"
                class="play-session-bar__jump"
                @click.prevent="jumpTo('play-multiplayer')"
            >Multiplayer</a>
            <span class="text-cod-gray-600 dark:text-cod-gray-500" aria-hidden="true">·</span>
            <button
                type="button"
                class="play-session-bar__jump"
                @click="openHotkeys()"
            >
                Hotkeys
            </button>
        </nav>
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
                <div class="flex justify-between gap-x-4"><dt class="font-mono text-cod-gray-300">Coin</dt><dd class="text-cod-gray-400">Insert coin (session bar)</dd></div>
                <div class="flex justify-between gap-x-4"><dt class="font-mono text-cod-gray-300">Start</dt><dd class="text-cod-gray-400">P1 Start (session bar)</dd></div>
                @endif
            </dl>
        </div>
    </div>
</div>
