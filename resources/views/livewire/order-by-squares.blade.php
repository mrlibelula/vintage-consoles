<div class="overflow-x-auto">
    <x-ribbon>
        @foreach ($selected_console['games'] as $game)
        <div class="h-[12rem] flex my-2 justify-center w-full lazy-load-container mr-4" data-loaded="false" data-url="{{ $this->gameRoute($game) }}">
            <livewire:game-card-classic :game="$game" :key="$game['id']" />
        </div>
        @endforeach
    </x-ribbon>

</div>