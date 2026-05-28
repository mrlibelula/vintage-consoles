<?php

namespace App\Livewire\Concerns;

use App\Models\Console;
use App\Services\GameRepository;
use Illuminate\Support\Collection;

trait RendersSortedConsoleGames
{
    public string $gameSortField = 'rating';

    public string $gameSortDirection = 'desc';

    protected function sortedConsoleGames(?Console $console): Collection
    {
        if (! $console) {
            return collect();
        }

        if (! $console->relationLoaded('games')) {
            $console->load([
                'games.genres',
                'games.screenshots' => fn ($query) => $query->orderBy('position'),
            ]);
        }

        return app(GameRepository::class)->sortGamesCollection(
            $console->games,
            $this->gameSortField,
            $this->gameSortDirection
        );
    }
}
