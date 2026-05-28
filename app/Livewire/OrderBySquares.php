<?php

namespace App\Livewire;

use App\Livewire\Concerns\RendersSortedConsoleGames;
use App\Models\Console;
use App\Models\Game;
use App\Service\Tool;
use Livewire\Component;

class OrderBySquares extends Component
{
    use RendersSortedConsoleGames;

    public ?Console $selected_console = null;

    public function gameRoute(Game $game): string
    {
        return Tool::gameRoute(
            $this->selected_console->toArray(),
            $game->toArray()
        );
    }

    public function render()
    {
        return view('livewire.order-by-squares', [
            'games' => $this->sortedConsoleGames($this->selected_console),
        ]);
    }
}
