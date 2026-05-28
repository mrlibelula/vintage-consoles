<?php

namespace App\Livewire;

use App\Models\Game;
use Livewire\Component;

class GameCardClassic extends Component
{
    public ?Game $game = null;

    public bool $showConsoleLabel = true;

    public function render()
    {
        return view('livewire.game-card-classic');
    }
}
