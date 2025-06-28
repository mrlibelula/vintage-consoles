<?php

namespace App\Livewire;

use App\Service\Tool;
use Livewire\Component;

class JsPlayer extends Component
{
    
    public $title;
    public $short_name;
    public $game_url;
    public $game_id;

    public function mount(string $enc_json_game, string $console_short_name)
    {
        $json_game = Tool::decode($enc_json_game);
        $game_data = json_decode($json_game, true);
        $this->title = $game_data['title'];
        $this->short_name = $console_short_name;
        $this->game_url = route('game.serve', [
            'console' => $console_short_name, 
            'filename' => $game_data['rom']
        ]);
        $this->game_id = $game_data['id'];
    }

    public function render()
    {
        return view('livewire.js-player')
            ->layout('layouts.player');
    }
}
