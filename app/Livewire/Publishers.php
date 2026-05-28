<?php

namespace App\Livewire;

use App\Livewire\Concerns\SortsGameCarousels;
use App\Service\Tool;
use App\Services\GameRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Publishers extends Component
{
    use SortsGameCarousels;

    public array $publishers = [];
    public string $publisher_name = '';
    public $filtered_games = [];
    public string $ob = 'group';

    public function mount(Request $request, string $publisher_name = ''): void
    {
        $this->ob = $request->has('ob')
            ? $request->query('ob')
            : Session::get('ob', $this->ob);
        Session::put('ob', $this->ob);

        $repo = app(GameRepository::class);

        $allPublishers  = $repo->getAllPublishers();
        $names          = array_column($allPublishers, 'name');
        $contains       = $publisher_name && in_array($publisher_name, $names, true);

        $this->publisher_name = $contains ? $publisher_name : '';
        $this->publishers     = $allPublishers;

        $this->filterGames($this->publisher_name);
    }

    public function filterGames(string $publisher_name): void
    {
        $repo = app(GameRepository::class);
        $this->filtered_games = $publisher_name
            ? $repo->getGamesByPublisher($publisher_name)
            : collect();
    }

    public function rendered(): void
    {
        Tool::loadersOff($this, [
            'loader-off',
            'loader-top-off',
            'skeleton-lista-off',
        ]);
        $this->dispatch('fixed-modal-loader-off');
    }

    public function render()
    {
        if (Session::exists('ob')) {
            $ob       = Session::get('ob');
            $this->ob = $ob === 'lista' ? $this->ob : $ob;
        }

        $this->loadGameSortFromSession();

        if ($this->filtered_games) {
            $this->filtered_games = $this->sortGamesForCarousel(collect($this->filtered_games));
        }

        return view('livewire.publishers');
    }
}
