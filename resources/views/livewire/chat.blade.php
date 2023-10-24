<div class="h-full">
    <!-- chat box -->
    <div class="flex flex-col h-full cursor-default bg-cod-gray-950 p-2">
        <div wire:poll="loadMessages" class="flex flex-col-reverse h-full overflow-scroll overflow-x-hidden">
            @foreach ($messages as $message)
            <x-chat-message :message="$message" />
            @endforeach
            <!-- welcome messages -->
            <div class=" text-base text-green-600">
                @auth Hi {{ Auth::user()->name }}, welcome to the platform.@endauth @guest <span class=" text-rose-500 text-base leading-tight">
                    You can sign in to have extra fun!
                </span> @endguest
            </div>
            <div class=" text-base leading-tight">
                Welcome to "{{ $game['title'] }}" live chat room.
            </div>
        </div>
    </div>
    
</div>