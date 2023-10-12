<?php

namespace App\Livewire;

use App\Service\Tool;
use Livewire\Component;
use Livewire\WithPagination;

class OrderByLista extends Component
{
    use WithPagination;

    public $selected_console;
    public int $paginate = 3;

    /**
     * Generates game route
     *
     * @param array $game
     * @return string
     */
    public function gameRoute(array $game): string
    {
        return Tool::gameRoute($this->selected_console, $game);
    }

    public function render()
    {
        $games = collect($this->selected_console['games'])->paginate($this->paginate);
        return view('livewire.order-by-lista', compact('games'));
    }
}
