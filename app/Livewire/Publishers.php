<?php

namespace App\Livewire;

use App\Service\Tool;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class Publishers extends Component
{
    public array $publishers = [];
    public string $publisher_name;
    public array $filtered_games = [];
    public string $ob = 'squares';
    
    public function mount(string $publisher_name = '', Request $request)
    {
        if ($request->has('ob')) {
            $this->ob = $request->query('ob');
        } else {
            $this->ob = Session::has('ob') ? Session::get('ob') : 'squares';
        }
        Session::put('ob', $this->ob);

        // new GameSession;
        $this->publishers();
        $this->filterGames($publisher_name);
    }

    public function filterGames(string $publisher_name)
    {
        $games = [];
        if ($publisher_name) {
            // filter games by publisher
            $consoles = Session::get('consoles') ?? [];
            foreach ($consoles as $console) {
                foreach ($console['games'] as $game) {
                    $found = strtolower($game['publisher']) === strtolower($publisher_name) ? $game : null;
                    if ($found) {
                        $game['console_id'] = $console['id'];
                        $game['console_short_name'] = $console['short_name'];
                        $games[] = $game;
                    }
                }
            }
        }
        $games = array_filter($games, fn ($game) => $game !== null);
        $this->filtered_games = Tool::sortBy($games, 'title', 'asc');
    }

    public function publishers()
    {
        $publishers = [];
        if (Session::has('consoles')) {
            $consoles = Session::get('consoles');
            foreach ($consoles as $console) {
                $games = $console['games'];
                foreach ($games as $game) {
                    $publishers[] = !in_array($game['publisher'], $publishers) ? $game['publisher'] : null;
                }
            }
            $publishers = array_filter($publishers, fn ($publisher) => $publisher !== null);
            // sort ignoring case sensitivity
            usort($publishers, function($a, $b) {
                return strcasecmp($a, $b);
            });
    
        }
        $this->publishers = $publishers;
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
            $this->ob = $ob === 'lista' ? 'squares' : $ob;
        }
        return view('livewire.publishers');
    }
}
