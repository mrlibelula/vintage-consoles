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

    public function updatedSearch()
    {
        if (!strlen($this->search)) {
            $this->clearSearchResults();
            return;
        }

        $consoles = Session::get('consoles');
        $results = [];
        $result_id = 0;

        foreach ($consoles as $console) {
            foreach ($console['games'] as $game) {
                if (str_contains(strtolower($game['title']), strtolower($this->search))) {
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
