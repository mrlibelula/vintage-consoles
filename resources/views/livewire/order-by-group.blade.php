<div class="overflow-x-auto">
    <!-- games ribbon (group) -->
    <div class="flex py-4 overflow-hidden overflow-x-auto flex-col">
        <div class="flex flex-no-wrap gap-x-4">
            @foreach ($selected_console['games'] as $game)
            <a wire:navigate.hover href="{{ $this->gameRoute($game) }}">
                <livewire:game-card :game="$game" :key="$game['id']" />
            </a>
            @endforeach
        </div>
    </div>

</div>