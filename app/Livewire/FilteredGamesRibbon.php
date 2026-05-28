<?php

namespace App\Livewire;

use App\Models\Game;
use Illuminate\Support\Collection;
use Livewire\Component;

class FilteredGamesRibbon extends Component
{
    /** @var array<int> */
    public array $gameIds = [];

    /** @var string group|squares */
    public string $ob = 'group';

    public function render()
    {
        $ob = in_array($this->ob, ['group', 'squares'], true) ? $this->ob : 'group';

        /** @var Collection<int, Game> $games */
        $games = Game::query()
            ->with(['console', 'genres', 'screenshots'])
            ->whereIn('id', $this->gameIds)
            ->get();

        // Preserve input order (parent already sorted by session settings).
        $byId = $games->keyBy('id');
        $games = collect($this->gameIds)
            ->map(fn (int $id) => $byId->get($id))
            ->filter()
            ->values();

        return view('livewire.filtered-games-ribbon', [
            'games' => $games,
            'ob' => $ob,
        ]);
    }
}

