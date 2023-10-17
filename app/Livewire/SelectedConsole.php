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
    public array $order_by = [];

    public function orderBy(string $order_by)
    {
        $this->clearOrderByExcept($order_by);
        Session::put('game_order_by', $this->order_by);
    }

    public function clearOrderByExcept(string $except)
    {
        $order_by = $this->order_by;
        foreach ($order_by as $key => $value) {
            $this->order_by[$key] = $except === $key ? true : false;
        }
    }

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
        // session order by list of games
        $this->order_by = Session::exists('game_order_by')
        ? Session::get('game_order_by')
        : [
            'group' => false,
            'squares' => false,
            'lista' => true,
        ];
    }

    public function render()
    {
        return view('livewire.selected-console');
    }
}
