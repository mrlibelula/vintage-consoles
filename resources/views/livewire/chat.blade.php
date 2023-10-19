<div class="h-full">
    <!-- chat box -->
    <div class="flex flex-col h-full cursor-default bg-cod-gray-950 p-2">
        <div wire:poll="loadMessages" class="flex flex-col-reverse h-full overflow-scroll overflow-x-hidden">
            @foreach ($messages as $message)
            <x-chat-message :message="$message" />
            @endforeach
            <!-- welcome messages -->
            <div class="flex flex-col">
                <div class="flex gap-x-3">
                    <div class="text-green-500 text-base leading-tight">
                        Libe(bot): <span class="text-base text-cod-gray-300 leading-tight">
                            @auth Hi {{ Auth::user()->name }}, @endauth welcome to the platform. @guest <span class=" text-rose-500 text-base leading-tight">
                                You can sign in to have extra fun!
                            </span> @endguest
                        </span>
                    </div>
                </div>
            </div>
            <div class=" text-base leading-tight">
                Welcome to "{{ $game['title'] }}" live chat.<br><br>
            </div>
        </div>
    </div>
    
</div>