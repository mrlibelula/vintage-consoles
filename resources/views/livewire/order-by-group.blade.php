<div class="overflow-x-auto">
    <x-ribbon ob="group">
    @foreach ($selected_console['games'] as $game)
        <swiper-slide>
            <a href="{{ $this->gameRoute($game) }}" 
                class="lazy-load-container" 
                data-loaded="false" 
            >
                <livewire:game-card :game="$game" :key="$game['id']" />
            </a>
        </swiper-slide>
    @endforeach
    </x-ribbon>
</div>