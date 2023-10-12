<?php

namespace App\Livewire;

use App\Service\Game;
use Livewire\Component;

class Play extends Component
{
    protected string $console_short_name;
    protected string $enc_game_id;
    public array $console;
    public array $game;

    public array $accordion_toggler = [
        'description' => false, 
        'genres' => true, 
        'screenshots' => true, 
    ];

    /**
     * 'game_title' intended for SEO purposes only
     * Irrelevant if present or not
     *
     * @param string $enc_game_id
     * @param string $console_short_name
     * @param string $game_title
     * @return void
     */
    public function mount(string $enc_game_id, string $console_short_name, string $game_title)
    {
        $game_obj = new Game($console_short_name, $enc_game_id);
        $this->console = $game_obj->getConsole();
        $this->game = $game_obj->getGame();
    }

    public function toggle(string $accordion_name)
    {
        $this->accordion_toggler[strtolower($accordion_name)] = !$this->accordion_toggler[strtolower($accordion_name)];
    }

    public function render()
    {
        return view('livewire.play');
    }
}
