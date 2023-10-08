<?php

namespace App\Livewire;

use Livewire\Component;

class SelectedConsole extends Component
{
    public bool $is_selected_tab_first = false;
    public bool $is_selected_tab_last = false;
    public array $selected_console = [];

    public function render()
    {
        return view('livewire.selected-console');
    }
}
