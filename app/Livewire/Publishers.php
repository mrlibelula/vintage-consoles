<?php

namespace App\Livewire;

use App\Service\Tool;
use Livewire\Component;
use App\Service\GameSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class Publishers extends Component
{
    public array $publishers = [];
    public string $publisher_name;
    public array $filtered_games = [];
    public string $ob = 'group';
    
    public function mount(Request $request, string $publisher_name = '')
    {
        if ($request->has('ob')) {
            $this->ob = $request->query('ob');
        } else {
            $this->ob = Session::has('ob') ? Session::get('ob') : $this->ob;
        }
        Session::put('ob', $this->ob);

        new GameSession;
        
        // verify if publisher exists in db
        $publishers = $this->publishers();
        $contains = false;
        if ($publisher_name) $contains = collect($publishers)->contains('name', $publisher_name);
        $this->publisher_name = $contains ? $publisher_name : '';
        $this->publishers = $publishers;
        $this->filterGames($this->publisher_name);
    }

    public function filterGames(string $publisher_name)
    {
        $games = [];
        if ($publisher_name) {
            // Use optimized session approach
            if (!Session::has('consoles_basic')) {
                new GameSession();
            }

            // Load full console data for filtering
            $gameSession = new GameSession();
            $consoles = $gameSession->getFullConsoleData();
            
            foreach ($consoles as $console) {
                if (isset($console['games'])) {
                    foreach ($console['games'] as $game) {
                        if (isset($game['publisher']) && strtolower($game['publisher']) === strtolower($publisher_name)) {
                            $game['console_id'] = $console['id'];
                            $game['console_short_name'] = $console['short_name'];
                            $games[] = $game;
                        }
                    }
                }
            }
            $games = Tool::sortBy($games, 'title', 'asc');
        }
        $this->filtered_games = $games;
    }

    public function publishers(): array
    {
        return Tool::publishers();
    }

    public function rendered()
    {
        Tool::loadersOff($this);
        $this->dispatch('fixed-modal-loader-off');
    }

    public function render()
    {
        if (Session::exists('ob')) {
            $ob = Session::get('ob');
            $this->ob = $ob === 'lista' ? $this->ob : $ob;
        }
        return view('livewire.publishers');
    }
}
