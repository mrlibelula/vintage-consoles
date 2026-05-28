<?php

namespace App\Livewire;

use App\Livewire\Concerns\SortsGameCarousels;
use App\Models\Console;
use App\Service\Tool;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class SelectedConsole extends Component
{
    use SortsGameCarousels;

    public bool $is_selected_tab_first = false;
    public bool $is_selected_tab_last = false;
    public ?Console $selected_console = null;
    public bool $console_data_accordion = true;
    public bool $specs_accordion = false;
    public bool $community_accordion = true;
    public string $ob = 'group';

    public function toggleAccordion(string $accordion_prop): void
    {
        $this->$accordion_prop = ! $this->$accordion_prop;
    }

    public function rendered(): void
    {
        Tool::loadersOff($this, [
            'loader-off',
            'loader-top-off',
            'skeleton-lista-off',
        ]);
    }

    public function render()
    {
        $this->ob = Session::exists('ob') ? Session::get('ob') : $this->ob;
        $this->loadGameSortFromSession();

        return view('livewire.selected-console');
    }
}
