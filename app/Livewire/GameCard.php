<?php

namespace App\Livewire;

use App\Models\Game;
use Livewire\Component;

class GameCard extends Component
{
    public ?Game $game = null;

    public function render()
    {
        return view('livewire.game-card');
    }
}
