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
     * Modified constructor to only require console name initially
     */
    public function __construct(string $console_short_name)
    {
        $this->loadConsole($console_short_name);
    }

    /**
     * Get the console data
     */
    public function getConsole(): array
    {
        return $this->console;
    }

    /**
     * Get the game data
     */
    public function getGame(): array
    {
        return $this->game;
    }

    private function game(string $enc_game_id): array
    {
        return Tool::findItemByKey($this->console['games'], 'id', Tool::decode(($enc_game_id)));
    }

    private function console(string $console_short_name): array
    {
        // Use optimized session approach - load full data only when needed
        if (!Session::has('consoles_basic')) {
            new GameSession;
        }
        
        // Get full console data including games from GameSession
        $gameSession = new GameSession();
        return $gameSession->getFullConsoleData($console_short_name);
    }

    private function loadConsole(string $console_short_name)
    {
        $this->console = $this->console($console_short_name);
    }
}