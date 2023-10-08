<a wire:navigate href="{{ route('dashboard') }}">
    <span class="sr-only">Libe.dev logo</span>
    <div class="flex items-center group">
        <x-libe-dev-logo class="w-[1.65rem] h-[1.65rem] rounded-none rounded-l" />
        <img class="ml-[-0.128rem] w-[4rem] rounded-r group-hover:brightness-105 transition duration-500 ease-in-out" src="{{ asset('images/games/nes_controller.png') }}" alt="">
    </div>
</a>