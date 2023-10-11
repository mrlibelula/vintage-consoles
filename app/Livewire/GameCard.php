<?php

namespace App\Livewire;

use Livewire\Component;

class GameCard extends Component
{
    public $game;

    public function render()
    {
        return view('livewire.game-card');
    }
}
