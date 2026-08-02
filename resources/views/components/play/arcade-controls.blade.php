@props(['console'])

@if (strtolower($console->short_name) === 'arcade')
<div
    class="mt-1 flex items-center gap-x-2 rounded-md bg-cod-gray-200/80 dark:bg-cod-gray-900/80 px-2 py-1.5"
    x-data="{
        send(action, down) {
            const iframe = document.getElementById('game-iframe');
            if (!iframe || !iframe.contentWindow) return;
            // Prefer RetroPad simulateInput in the player (select=coin, start=P1).
            // Do not synthesize Digit1/5 — EmulatorJS binds 1/2/3 to quick-save hotkeys.
            if (!action) return;
            iframe.contentWindow.postMessage({
                type: 'vintage-arcade-key',
                action,
                down,
            }, window.location.origin);
        },
        press(action, event) {
            event.preventDefault();
            this.send(action, true);
        },
        release(action, event) {
            event.preventDefault();
            this.send(action, false);
        },
    }"
>
    <span class="hidden sm:inline text-xs uppercase tracking-wide text-cod-gray-600 dark:text-cod-gray-400 mr-1">Arcade</span>
    <button
        type="button"
        class="rounded bg-rose-700 hover:bg-rose-600 px-3 py-1 text-sm text-white shadow select-none"
        @pointerdown="press('coin', $event)"
        @pointerup="release('coin', $event)"
        @pointerleave="release('coin', $event)"
        @pointercancel="release('coin', $event)"
    >
        Insert Coin
    </button>
    <button
        type="button"
        class="rounded bg-cod-gray-700 hover:bg-cod-gray-600 dark:bg-cod-gray-700 dark:hover:bg-cod-gray-600 px-3 py-1 text-sm text-white shadow select-none"
        @pointerdown="press('start', $event)"
        @pointerup="release('start', $event)"
        @pointerleave="release('start', $event)"
        @pointercancel="release('start', $event)"
    >
        Start
    </button>
</div>
@endif
