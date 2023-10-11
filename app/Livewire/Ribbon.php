<?php

namespace App\Livewire;

use Livewire\Component;

class Ribbon extends Component
{
    public $data = [];
    public $template;
    public $template_var_name;

    public function render()
    {
        return view('livewire.ribbon');
    }
}
