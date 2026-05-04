<?php

namespace App\Livewire;

use App\Service\GameSession;
use App\Service\Tool;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Navigation extends Component
{
    public string $search = '';
    public array $search_results = [];
    private static $searchCache = null; // Cache search data

    protected $listeners = ['refreshSearchData'];

    public function updatedSearch()
    {
        // Clear results if search is too short to avoid excessive loading
        if (strlen($this->search) < 2) {
            $this->clearSearchResults();
            return;
        }

        // Use static cache to avoid repeated full data loads
        if (self::$searchCache === null) {
            // Only load full data once per request/session
            if (!Session::has('consoles')) {
                new GameSession();
            }
            
            $gameSession = new GameSession();
            self::$searchCache = $gameSession->getFullConsoleData();
        }
        
        $results = [];
        $result_id = 0;
        $searchLower = strtolower($this->search);

        foreach (self::$searchCache as $console) {
            foreach ($console['games'] ?? [] as $game) {
                if (str_contains(strtolower($game['title']), $searchLower)) {
                    $results[] = [
                        'result_id' => $result_id++,
                        'console_id' => $console['id'],
                        'console_short_name' => $console['short_name'],
                        'console_long_name' => $console['long_name'],
                        'console_console_icon' => $console['console_icon'],
                        'console_console_logo' => $console['console_logo'],
                        'game_id' => $game['id'],
                        'game_title' => $game['title'],
                        'game_box' => $game['box'],
                        'game_poster' => $game['poster'],
                        'game_cartridge' => $game['cartridge'],
                        'game_rating' => $game['rating'],
                    ];
                }
                
                // Limit results to prevent excessive memory usage
                if (count($results) >= 50) {
                    break 2; // Break both loops
                }
            }
        }

        $results = collect($results)->sortBy('game_title')->toArray();
        $this->search_results = $results;

        $this->dispatch('loader-top-off');
    }

    public function clearSearchResults()
    {
        $this->search = '';
        $this->search_results = [];
    }

    /**
     * Refresh search data by reloading session data
     */
    public function refreshSearchData()
    {
        // Clear static cache to force fresh data load
        self::$searchCache = null;
        
        // If there's an active search, re-run it with fresh data
        if (!empty($this->search)) {
            $this->updatedSearch();
        }
    }

    /**
     * Generates game route
     *
     * @param array $console
     * @param array $game
     * @return string
     */
    public function gameRoute(array $console, array $game): string
    {
        return Tool::gameRoute($console, $game);
    }

    public function render()
    {
        return view('livewire.navigation');
    }
}
