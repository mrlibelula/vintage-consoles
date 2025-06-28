<div class="overflow-x-auto">
    <x-ribbon ob="squares">
        @foreach ($selected_console['games'] as $game)
        <swiper-slide>
            <a href="{{ $this->gameRoute($game) }}"
                @click="$dispatch('loader-top-on')"
                class="h-[12rem] flex my-2 justify-center w-full lazy-load-container" 
                data-loaded="false" 
            >
                <livewire:game-card-classic :game="$game" :key="$game['id']" />
            </a>
        </swiper-slide>
        @endforeach
    </x-ribbon>

</div>