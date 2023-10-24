<x-ribbon>
    @foreach ($selected_console['games'] as $game)
    <a wire:navigate href="{{ $this->gameRoute($game) }}" class="h-[12rem] flex my-2 justify-center w-full">
        <livewire:game-card-classic :game="$game" :key="$game['id']" class="p-4" />
    </a>
    @endforeach
</x-ribbon>