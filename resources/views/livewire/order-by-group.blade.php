<div class="overflow-x-auto">
    <!-- games ribbon (group) -->
    <x-ribbon>
    @foreach ($selected_console['games'] as $game)
        <div class="lazy-load-container mr-4" data-loaded="false" data-url="{{ $this->gameRoute($game) }}">
            <livewire:game-card :game="$game" :key="$game['id']" />
        </div>
    @endforeach
    </x-ribbon>
</div>