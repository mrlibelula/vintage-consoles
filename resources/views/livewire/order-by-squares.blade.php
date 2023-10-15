<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-2 mt-2">
    @foreach ($selected_console['games'] as $game)
    <a _wire:navigate.hover_ href="{{ $this->gameRoute($game) }}" class="h-[12rem] flex my-2 justify-center w-full">
        <livewire:game-card-classic :game="$game" :key="$game['id']" class="p-4" />
    </a>
    @endforeach
</div>