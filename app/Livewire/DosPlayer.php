<?php

namespace App\Livewire;

use Livewire\Component;
use App\Service\Tool;

class DosPlayer extends Component
{
    public $enc_json_game;
    public $console_short_name;
    public $game;

    public function mount(string $enc_json_game, string $console_short_name)
    {
        $this->enc_json_game = $enc_json_game;
        $this->console_short_name = $console_short_name;
        $this->game = json_decode(Tool::decode($this->enc_json_game), true);
    }

    public function render()
    {
        return view('livewire.dos-player')
            ->layout('layouts.player');
    }
}
