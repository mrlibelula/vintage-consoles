<?php

namespace App\Livewire;

use Livewire\Component;

class OrderBySquares extends Component
{
    public array $selected_console;
    
    public function render()
    {
        return view('livewire.order-by-squares');
    }
}
