<?php

namespace App\Livewire;

use App\Service\Tool;
use Livewire\Component;
use App\Service\GameSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class Genres extends Component
{
    public string $genre_name;
    public array $genres = [];
    public array $filtered_games = [];
    public string $ob = 'group';
    public array $games = [];
    public string $current_genre = '';

    public function mount(Request $request, string $genre_name = '')
    {
        if ($request->has('ob')) {
            $this->ob = $request->query('ob');
        } else {
            $this->ob = Session::has('ob') ? Session::get('ob') : $this->ob;
        }
        Session::put('ob', $this->ob);

        new GameSession;

        // verify if genre exists in db
        $contains = false;
        $genres = $this->genres();
        if ($genre_name) $contains = collect($genres)->contains('name', $genre_name);
        $this->genre_name = $contains
            ? $genre_name
            : '';
        $this->genres = $genres;
        $this->filterGames($this->genre_name);
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

    public function filterGames(string $genre_name)
    {
        $games = [];
        if ($genre_name) {
            // Use optimized session approach
            if (!Session::has('consoles')) {
                new GameSession();
            }

            // Load full console data for filtering
            $gameSession = new GameSession();
            $consoles = $gameSession->getFullConsoleData();
            
            foreach ($consoles as $console) {
                if (isset($console['games'])) {
                    foreach ($console['games'] as $game) {
                        $found = Tool::findItemByKey($game['genres'], 'name', strtolower($genre_name));
                        if ($found) {
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

    public function genres(): array
    {
        return Tool::genres();
    }

    public function rendered()
    {
        Tool::loadersOff($this, [
            'loader-off',
            'loader-top-off',
            'skeleton-lista-off',
        ]);
        // Defer ribbon skeleton off so Alpine can apply skeleton-*-on after wire:navigate morph.
        $this->js("requestAnimationFrame(() => requestAnimationFrame(() => {
            window.dispatchEvent(new CustomEvent('skeleton-group-off'));
            window.dispatchEvent(new CustomEvent('skeleton-square-off'));
        }))");
        $this->dispatch('fixed-modal-loader-off');
    }

    public function render()
    {
        if (Session::exists('ob')) {
            $ob = Session::get('ob');
            $this->ob = $ob === 'lista' ? 'squares' : $ob;
        }
        return view('livewire.genres');
    }

    /**
     * Filter component games from consoles via genre name
     *
     * @param string $genre_name
     * @return void
     */
    public function filterByGenre(string $genre_name): void
    {
        $this->current_genre = $genre_name;
        $this->games = [];
        
        // Use optimized session - get basic console info first
        if (!Session::has('consoles')) {
            new GameSession();
        }

        // Load full console data only when filtering is needed
        $gameSession = new GameSession();
        $consoles = $gameSession->getFullConsoleData();

        foreach ($consoles as $console) {
            if (isset($console['games'])) {
                foreach ($console['games'] as $game) {
                    if (isset($game['genres'])) {
                        foreach ($game['genres'] as $genre) {
                            if (strtolower($genre['name']) === strtolower($genre_name)) {
                                $this->games[] = array_merge($game, [
                                    'console_short_name' => $console['short_name'],
                                    'console_long_name' => $console['long_name']
                                ]);
                                break 2; // Break out of both genre and game loops for this game
                            }
                        }
                    }
                }
            }
        }

        $this->dispatchBrowserEvent('loader-off');
    }
}
