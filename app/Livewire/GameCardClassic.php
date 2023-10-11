<?php

namespace App\Livewire;

use Livewire\Component;

class GameCardClassic extends Component
{
    public $game;
    
    public function render()
    {
        return view('livewire.game-card-classic');
    }
}
