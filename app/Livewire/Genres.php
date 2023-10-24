<?php

namespace App\Livewire;

use App\Service\GameSession;
use App\Service\Tool;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Genres extends Component
{
    public string $genre_name;
    public array $genres = [];
    public array $filtered_games = [];
    public array $order_by = [
        'group' => true,
        'squares' => false,
    ];

    public function orderBy(string $list_type)
    {
        $this->order_by = array_map(function ($by) {
            return $by = false;
        }, $this->order_by);
        
        $this->order_by[strtolower($list_type)] = true;
        Session::put('genres_sort', $this->order_by);
    }

    public function mount(string $genre_name = '')
    {
        new GameSession;

        // verify if genre exists in db
        $contains = false;
        $genres = $this->genres();
        if ($genre_name) $contains = collect($genres)->contains($genre_name);
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
            // filter games
            $consoles = Session::get('consoles') ?? [];
            foreach ($consoles as $console) {
                foreach ($console['games'] as $game) {
                    $found = Tool::findItemByKey($game['genres'], 'name', strtolower($genre_name));
                    if ($found) {
                        $game['console_id'] = $console['id'];
                        $game['console_short_name'] = $console['short_name'];
                        $games[] = $game;
                    }
                }
            }
            $games = Tool::sortBy($games, 'title', 'asc');
        }
        $this->filtered_games = $games;
    }

    public function genres(): array
    {
        return Tool::getGenres();
    }

    public function rendered()
    {
        Tool::loadersOff($this);
        $this->dispatch('fixed-modal-loader-off');
    }

    public function render()
    {
        if (Session::exists('genres_sort')) {
            $this->order_by = Session::get('genres_sort');
        }
        
        return view('livewire.genres');
    }
}
