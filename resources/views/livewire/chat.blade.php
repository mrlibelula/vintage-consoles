<div class="h-full">
    <!-- chat box -->
    <div class="flex flex-col h-full cursor-default bg-cod-gray-50 text-cod-gray-700 dark:bg-cod-gray-950 dark:text-cod-gray-400 p-2">
        <div wire:poll="loadMessages" class="flex flex-col-reverse h-full overflow-scroll overflow-x-hidden">
            @foreach ($messages as $message)
            <x-chat-message :message="$message" />
            @endforeach
            <!-- welcome messages -->
            <div class="text-base text-green-700 dark:text-green-500">
                @auth Hi {{ Auth::user()->name }}, welcome to the platform.@endauth @guest <span class="text-rose-600 dark:text-rose-500 text-base leading-tight">
                    You can sign in to have extra fun!
                </span> @endguest
            </div>
            <div class="text-base leading-tight text-cod-gray-600 dark:text-cod-gray-500">
                Welcome to "{{ $game['title'] }}" live chat room.
            </div>
        </div>
    </div>
</div>