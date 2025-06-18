<?php

namespace App\Livewire;

use App\Service\Tool;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class SelectedConsole extends Component
{    
    public bool $is_selected_tab_first = false;
    public bool $is_selected_tab_last = false;
    public array $selected_console = [];
    public bool $console_data_accordion = true;
    public bool $specs_accordion = false;
    public bool $community_accordion = true;
    public string $ob = 'group';

    public function toggleAccordion(string $accordion_prop)
    {
        $this->$accordion_prop = !$this->$accordion_prop;
    }

    public function rendered()
    {
        Tool::loadersOff($this);
    }

    public function mount()
    {
        //
    }

    public function render()
    {
        $this->ob = Session::exists('ob') ? Session::get('ob') : $this->ob;
        return view('livewire.selected-console');
    }
}
