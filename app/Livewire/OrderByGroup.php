<?php

namespace App\Livewire;

use Livewire\Component;

class OrderByGroup extends Component
{
    public array $selected_console;

    public function render()
    {
        return view('livewire.order-by-group');
    }
}
