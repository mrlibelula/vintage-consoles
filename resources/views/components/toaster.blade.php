{{-- Global toast stack (above modals z-[100]). Listens for Livewire/Alpine `toast` events. --}}
<div
    x-data="{
        toasts: [],
        add(event) {
            const raw = event.detail;
            const detail = Array.isArray(raw) ? (raw[0] ?? {}) : (raw ?? {});
            const message = detail.message ?? '';
            if (! message) return;

            const id = `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
            const type = ['success', 'error', 'warning', 'info'].includes(detail.type)
                ? detail.type
                : 'info';

            this.toasts.push({ id, type, message });
            window.setTimeout(() => this.remove(id), detail.duration ?? 5500);
        },
        remove(id) {
            this.toasts = this.toasts.filter((t) => t.id !== id);
        },
    }"
    x-on:toast.window="add($event)"
    class="pointer-events-none fixed inset-x-0 top-4 z-[200] flex flex-col items-end gap-2 px-4 sm:top-5 sm:px-6"
    role="region"
    aria-label="Notifications"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1 sm:translate-x-2 sm:translate-y-0"
            x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto flex w-full max-w-md items-start gap-x-3 rounded-lg border px-4 py-3 shadow-lg backdrop-blur-sm"
            :class="{
                'border-green-500/40 bg-green-50 text-green-900 dark:border-green-500/30 dark:bg-green-950/95 dark:text-green-100': toast.type === 'success',
                'border-red-500/40 bg-red-50 text-red-900 dark:border-red-500/30 dark:bg-red-950/95 dark:text-red-100': toast.type === 'error',
                'border-amber-500/40 bg-amber-50 text-amber-950 dark:border-amber-500/30 dark:bg-amber-950/95 dark:text-amber-100': toast.type === 'warning',
                'border-sky-500/40 bg-sky-50 text-sky-950 dark:border-sky-500/30 dark:bg-sky-950/95 dark:text-sky-100': toast.type === 'info',
            }"
            role="status"
        >
            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full"
                :class="{
                    'bg-green-500/15 text-green-700 dark:text-green-300': toast.type === 'success',
                    'bg-red-500/15 text-red-700 dark:text-red-300': toast.type === 'error',
                    'bg-amber-500/15 text-amber-800 dark:text-amber-300': toast.type === 'warning',
                    'bg-sky-500/15 text-sky-800 dark:text-sky-300': toast.type === 'info',
                }"
            >
                <svg x-show="toast.type === 'success'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg x-show="toast.type === 'error'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <svg x-show="toast.type === 'warning'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <svg x-show="toast.type === 'info'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>

            <p class="min-w-0 flex-1 text-xl leading-snug" x-text="toast.message"></p>

            <button
                type="button"
                class="shrink-0 rounded p-1 opacity-60 hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-current"
                @click="remove(toast.id)"
                aria-label="Dismiss"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>
