<a wire:navigate href="{{ route('home') }}">
    <span class="sr-only">Libe.dev logo</span>
    <div class="flex items-center group">
        <div>
            <x-libe-dev-logo class="w-[1.65rem] h-[1.65rem] rounded-none rounded-l" />
        </div>
        <img class="ml-[-0.128rem] w-[4rem] rounded-r group-hover:brightness-105 smooth-500" src="{{ asset('/images/games/nes_controller.png') }}" alt="">
    </div>
</a>