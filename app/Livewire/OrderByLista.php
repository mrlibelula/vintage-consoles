<?php

namespace App\Livewire;

use Livewire\Component;

class OrderByLista extends Component
{
    public $selected_console;
    
    public function render()
    {
        return view('livewire.order-by-lista');
    }
}
