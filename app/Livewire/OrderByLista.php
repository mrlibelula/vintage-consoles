<?php

namespace App\Livewire;

use App\Livewire\Concerns\RendersSortedConsoleGames;
use App\Models\Console;
use App\Models\Game;
use App\Service\Tool;
use Livewire\Component;
use Livewire\WithPagination;

class OrderByLista extends Component
{
    use RendersSortedConsoleGames;
    use WithPagination;

    public ?Console $selected_console = null;
    public int $paginate = 3;

    public function gameRoute(Game $game): string
    {
        return Tool::gameRoute(
            $this->selected_console->toArray(),
            $game->toArray()
        );
    }

    public function updatedPage(): void
    {
        Tool::loadersOff($this);
    }

    public function render()
    {
        if (! $this->selected_console) {
            $games = collect()->paginate($this->paginate);

            return view('livewire.order-by-lista', compact('games'));
        }

        $field = in_array($this->gameSortField, ['title', 'rating'], true)
            ? $this->gameSortField
            : 'rating';
        $direction = in_array($this->gameSortDirection, ['asc', 'desc'], true)
            ? $this->gameSortDirection
            : 'desc';

        $games = $this->selected_console->games()
            ->with(['genres', 'screenshots'])
            ->orderBy($field, $direction)
            ->paginate($this->paginate);

        return view('livewire.order-by-lista', compact('games'));
    }
}
