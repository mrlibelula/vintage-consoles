<div class="play-chat-room flex h-full min-h-0 flex-1 flex-col overflow-hidden text-cod-gray-700 dark:text-cod-gray-400">
    {{-- Header --}}
    <div class="flex shrink-0 items-center justify-between gap-x-2 border-b border-cod-gray-400/80 px-2 py-1 dark:border-cod-gray-700">
        <div class="flex min-w-0 items-center gap-x-1.5">
            <x-pixelarticon name="message" :size="22" class="text-rose-600 dark:text-rose-400" />
            <span class="truncate text-base leading-none text-cod-gray-800 dark:text-cod-gray-200">Live chat</span>
        </div>
        <div class="flex shrink-0 items-center gap-x-1 text-cod-gray-500 dark:text-cod-gray-500" title="{{ count($online) }} online">
            <x-pixelarticon name="user" :size="20" class="opacity-80" />
            <span class="text-sm leading-none tabular-nums">{{ count($online) }}</span>
        </div>
    </div>

    {{-- Body: messages + presence --}}
    <div class="flex min-h-0 flex-1">
        <div wire:poll="loadMessages" class="flex min-h-0 min-w-0 flex-1 flex-col-reverse overflow-x-hidden overflow-y-auto px-2 py-1.5">
            @foreach ($messages as $message)
            <x-chat-message :message="$message" />
            @endforeach
            <div class="mt-1 text-base leading-tight text-green-700 dark:text-green-500">
                @auth
                Hi {{ Auth::user()->name }}, welcome to the platform.
                @endauth
                @guest
                <span class="text-rose-600 dark:text-rose-500">You can sign in to have extra fun!</span>
                @endguest
            </div>
            <div class="text-base leading-tight text-cod-gray-600 dark:text-cod-gray-500">
                Welcome to "{{ $game['title'] }}" live chat room.
            </div>
        </div>

        <aside
            class="play-chat-presence flex w-12 shrink-0 flex-col gap-y-1 overflow-y-auto border-l border-cod-gray-400/80 px-1 py-1.5 sm:w-[5.75rem] dark:border-cod-gray-700"
            aria-label="Online now"
        >
            @forelse ($online as $viewer)
            <div
                class="flex items-center gap-x-1 rounded px-0.5 py-0.5"
                title="{{ $viewer['name'] }}"
            >
                <span @class([
                    'flex h-6 w-6 shrink-0 items-center justify-center',
                    'text-sky-600 dark:text-sky-400' => $viewer['user_id'] !== null,
                    'text-cod-gray-500 dark:text-cod-gray-500' => $viewer['user_id'] === null,
                ])>
                    <x-pixelarticon :name="$viewer['icon']" :size="20" />
                </span>
                <span class="hidden min-w-0 truncate text-sm leading-none text-cod-gray-700 dark:text-cod-gray-300 sm:inline">
                    {{ $viewer['name'] }}
                </span>
            </div>
            @empty
            <div class="px-0.5 text-sm leading-tight text-cod-gray-400 dark:text-cod-gray-600">
                —
            </div>
            @endforelse
        </aside>
    </div>
</div>
