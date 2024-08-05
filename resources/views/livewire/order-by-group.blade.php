<div class="overflow-x-auto">
    <!-- games ribbon (group) -->
    <x-ribbon>
    @foreach ($selected_console['games'] as $game)
        <div class="lazy-load-container mr-4" data-loaded="false">
            <livewire:game-card :game="$game" :key="$game['id']" />
        </div>
        {{-- <a wire:navigate href="{{ $this->gameRoute($game) }}" class="lazy-load-container" data-loaded="false">
            <livewire:game-card :game="$game" :key="$game['id']" />
        </a> --}}
    @endforeach
    </x-ribbon>
    {{-- <div class="flex py-4 overflow-hidden overflow-x-auto flex-col">
        <div class="flex flex-no-wrap gap-x-4">
            @foreach ($selected_console['games'] as $game)
            <a wire:navigate href="{{ $this->gameRoute($game) }}" class="lazy-load-container" data-loaded="false">
                <livewire:game-card :game="$game" :key="$game['id']" />
            </a>
            @endforeach
        </div>
    </div> --}}
</div>