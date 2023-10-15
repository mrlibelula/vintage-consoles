<?php

namespace App\Livewire;

use App\Service\Game;
use App\Service\Tool;
use Livewire\Component;

class Play extends Component
{
    protected string $console_short_name;
    protected string $enc_game_id;
    public array $console;
    public array $game;
    public string $game_url;
    public string $player_route;

    public array $accordion_toggler = [
        'description' => false, 
        'genres' => false, 
        'screenshots' => false, 
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

        $this->loadGameUrl();
        
        $this->player_route = route('player', [
            Tool::encode(json_encode($this->game)),
            strtolower($this->console['short_name']),
        ]);
    }

    public function toggle(string $accordion_name)
    {
        $this->accordion_toggler[strtolower($accordion_name)] = !$this->accordion_toggler[strtolower($accordion_name)];
    }

    /**
     * For all consoles except 'PC'
     *
     * @return void
     */
    public function loadGameUrl()
    {
        $console_short_name = strtolower($this->console['short_name']);
        if ($console_short_name !== 'pc') {
            $this->game_url = 'games/' . $console_short_name . '/' . $this->game['rom'];
        }
    }
    
    public function render()
    {
        return view('livewire.play');
    }
}
