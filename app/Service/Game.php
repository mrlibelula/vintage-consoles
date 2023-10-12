<?php

namespace App\Service;

use Illuminate\Support\Facades\Session;

class Game
{
    protected string $console_short_name;
    protected int $game_id;

    protected array $console;
    protected array $game;

    /**
     * Obtains Game data from Session
     *
     * @param string $console_short_name
     * @param string $enc_game_id
     */
    public function __construct(string $console_short_name, string $enc_game_id)
    {
        $this->console = $this->console($console_short_name);
        $this->game = $this->game($enc_game_id);
    }

    private function game(string $enc_game_id): array
    {
        return Tool::findItemByKey($this->console['games'], 'id', Tool::decode(($enc_game_id)));
    }

    private function console(string $console_short_name): array
    {
        return Tool::findItemByKey(Session::get('consoles'), 'short_name', $console_short_name);
    }

    public function getConsole(): array
    {
        return $this->console;
    }
    
    public function getGame(): array
    {
        return $this->game;
    }
}