<?php

namespace App\Livewire;

use Livewire\Component;

class RibbonGames extends Component
{
    public $games;

    public function render()
    {
        return view('livewire.ribbon-games');
    }
}
