<div class="overflow-x-auto">
    <x-ribbon>
        @foreach ($selected_console['games'] as $game)
        <div class="h-[12rem] flex my-2 justify-center w-full lazy-load-container mr-4" data-loaded="false">
            <livewire:game-card-classic :game="$game" :key="$game['id']" />
        </div>
        {{-- <a wire:navigate href="{{ $this->gameRoute($game) }}" class="h-[12rem] flex my-2 justify-center w-full lazy-load-container" data-loaded="false">
            <livewire:game-card-classic :game="$game" :key="$game['id']" class="p-4" />
        </a> --}}
        @endforeach
    </x-ribbon>

</div>